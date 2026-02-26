<?php

namespace App\Http\Controllers;

use App\Services\ClaimService;
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClaimController extends Controller
{
    protected $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    // Role User: Membuat klaim baru [cite: 39]
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validasi gagal',
                'errors' => $validate->errors()
            ], 422);
        }

        try {
            // Siapkan data array
            $data = $request->only(['title', 'description', 'amount']);

            // Handle file upload jika ada
            if ($request->hasFile('attachment')) {
                // Simpan ke storage/app/public/claims
                $path = $request->file('attachment')->store('claims', 'public');
                $data['attachment_path'] = $path;
            }

            $claim = $this->claimService->createClaim($data, $request->user()->id);

            return response()->json([
                "code" => 200,
                "message" => "Klaim berhasil dibuat",
                "data" => $claim
            ], 200);

        } catch (\Exception $e) {
            $message = $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine();
            Log::critical($message);

            return response()->json([
                'code' => 500,
                'message' => 'Gagal membuat klaim',
                'errors' => $message
            ], 500);
        }
    }

    // Role User: Melihat daftar klaim yang dimiliki sendiri
    public function myClaims(Request $request)
    {
        try {
            $claims = Claim::where('user_id', $request->user()->id)->get();
            return response()->json([
                'code' => 200,
                'message' => 'Get Data Berhasil',
                'data' => $claims
            ], 200);
        } catch (\Exception $e) {
            $message = $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine();
            Log::critical($message);

            return response()->json([
                'code' => 500,
                'message' => 'Gagal mengambil klaim',
                'errors' => $message
            ], 500);
        }

    }

    // Role Verifier: Melihat klaim berstatus submitted
    public function getSubmitted()
    {
        try {
            $claims = Claim::where('status', 'submitted')->with('user')->get();
            return response()->json([
                'code' => 200,
                'message' => 'Get Data Berhasil',
                'data' => $claims
            ], 200);
        } catch (\Exception $e) {
            $message = $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine();
            Log::critical($message);

            return response()->json([
                'code' => 500,
                'message' => 'Gagal mengambil klaim',
                'errors' => $message
            ], 500);
        }
    }

    // Role Approver: Melihat klaim berstatus reviewed
    public function getReviewed()
    {
        try {
            $claims = Claim::where('status', 'reviewed')->with('user')->get();
            return response()->json([
                'code' => 200,
                'message' => 'Get Data Berhasil',
                'data' => $claims
            ], 200);
        } catch (\Exception $e) {
            $message = $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine();
            Log::critical($message);

            return response()->json([
                'code' => 500,
                'message' => 'Gagal mengambil klaim',
                'errors' => $message
            ], 500);
        }
    }

    // Method umum untuk update status (dipakai oleh semua role sesuai routing)
    public function changeStatus(Request $request, $id)
    {
        // 1. Validasi input dari Frontend
        $validated = Validator::make($request->all(), [
            'status' => 'required|in:submitted,reviewed,approved,rejected'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validasi gagal',
                'errors' => $validated->errors()
            ], 422);
        }

        $newStatus = $request->status;
        $userRole = $request->user()->role->name; // Asumsi relasi role sudah ada

        // 2. Validasi RBAC (Role-Based Access Control)
        $this->authorizeRoleAction($userRole, $newStatus);

        // 3. Eksekusi Service
        try {
            $claim = $this->claimService->updateStatus($id, $newStatus, $request->user()->id);
            return response()->json([
                'success' => true,
                'message' => "Status berhasil diubah menjadi {$newStatus}",
                'data' => $claim
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Memastikan setiap Role hanya bisa mengupdate ke status yang diizinkan
     */
    private function authorizeRoleAction(string $role, string $newStatus)
    {
        $allowedActions = [
            'User' => ['submitted'], // User hanya bisa submit
            'Verifier' => ['reviewed'], // Verifier hanya bisa me-review
            'Approver' => ['approved', 'rejected'], // Approver mengambil keputusan akhir
        ];

        if (!in_array($newStatus, $allowedActions[$role] ?? [])) {
            throw new \Exception("Role {$role} tidak memiliki izin untuk mengubah status menjadi {$newStatus}");
        }
    }

    // Method untuk mengambil data statistik Chart & Cards
    public function getStats(Request $request)
    {
        try {
            $stats = $this->claimService->getClaimStats($request->user());

            return response()->json([
                'code' => 200,
                'message' => 'Get Stats Berhasil',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            $message = $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine();
            Log::critical($message);

            return response()->json([
                'code' => 500,
                'message' => 'Gagal mengambil statistik klaim',
                'errors' => $message
            ], 500);
        }
    }
}
