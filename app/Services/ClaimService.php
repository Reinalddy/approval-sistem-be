<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\ClaimLogs;
use Illuminate\Support\Facades\DB;
use Exception;

class ClaimService
{
    /**
     * Membuat klaim baru (Draft)
     */
    public function createClaim(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $claim = Claim::create([
                'user_id' => $userId,
                'title' => $data['title'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'status' => 'draft',
                'attachment_path' => $data['attachment_path'] ?? null,
            ]);

            return $claim;
        });
    }

    /**
     * Update status dengan Pessimistic Locking dan Log
     */
    public function updateStatus(int $claimId, string $newStatus, int $userId)
    {
        DB::beginTransaction();

        try {
            // lockForUpdate() mencegah race condition [cite: 55]
            $claim = Claim::where('id', $claimId)->lockForUpdate()->firstOrFail();
            $oldStatus = $claim->status;

            // Validasi transisi hanya bisa berurutan
            $this->validateTransition($oldStatus, $newStatus);

            $claim->status = $newStatus;
            $claim->save();

            // Setiap kali terjadi perubahan status klaim, system wajib menyimpan activity log [cite: 41]
            ClaimLogs::create([
                'claim_id' => $claim->id,
                'user_id' => $userId,
                'before_status' => $oldStatus,
                'after_status' => $newStatus,
            ]);

            DB::commit();
            return $claim;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e; // Lempar ke controller untuk di-handle
        }
    }

    /**
     * Validasi urutan status klaim: draft -> submitted -> reviewed -> approved / rejected [cite: 24]
     */
    private function validateTransition(string $current, string $next)
    {
        $validTransitions = [
            'draft' => ['submitted'],
            'submitted' => ['reviewed'],
            'reviewed' => ['approved', 'rejected'],
        ];

        if (!isset($validTransitions[$current]) || !in_array($next, $validTransitions[$current])) {
            throw new \Exception("Transisi status dari {$current} ke {$next} tidak valid. Transisi harus berurutan.");
        }
    }
}