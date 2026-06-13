<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\Database;
use Throwable;

/**
 * Trait DatabaseTransactionTrait
 *
 * Provides a standardized, reusable transaction wrapper that eliminates
 * the boilerplate pattern duplicated across every admin sub-service.
 *
 * Usage:
 *   return $this->wrapInTransaction(fn() => $this->model->save($entity));
 *
 * @package App\Libraries
 */
trait DatabaseTransactionTrait
{
    /**
     * Wraps a single-database-operation call inside a transaction with
     * automatic rollback and logging on failure.
     *
     * @param callable $operation The database operation to execute.
     * @param string   $log_label A human-readable label for error logging.
     *
     * @return array{status: string, message: string}
     */
    private function wrapInTransaction(callable $operation, string $log_label): array
    {
        $db = Database::connect();
        $db->transStart();

        try {
            $operation();
            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['status' => 'error', 'message' => "Transaction failed while {$log_label}."];
            }

            return ['status' => 'success', 'message' => ucfirst("{$log_label} successfully.")];
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', "Failed to {$log_label}", [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
