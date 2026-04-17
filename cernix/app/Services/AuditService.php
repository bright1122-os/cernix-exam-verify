<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditService
{
    /**
     * Append an entry to the audit_log table.
     *
     * This is the ONLY write operation permitted on audit_log.
     * Records are never updated or deleted — the table is append-only by design.
     *
     * @param  string $actorId    Identifier of the entity performing the action
     *                            (matric_no for students, examiner_id for examiners,
     *                             "system" for automated processes)
     * @param  string $actorType  Category of actor: "student" | "examiner" | "admin" | "system"
     * @param  string $action     Short dot-namespaced event name, e.g. "token.issued",
     *                            "payment.verified", "token.revoked"
     * @param  array  $metadata   Arbitrary key-value context; must be JSON-serialisable
     */
    public function logAction(
        string $actorId,
        string $actorType,
        string $action,
        array  $metadata = []
    ): void {
        DB::table('audit_log')->insert([
            'actor_id'   => $actorId,
            'actor_type' => $actorType,
            'action'     => $action,
            'metadata'   => $this->encodeMetadata($metadata),
            'timestamp'  => $this->now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Safely JSON-encode metadata, masking any value that cannot be serialised
     * so a bad metadata array never causes a log entry to be silently dropped.
     */
    public function encodeMetadata(array $metadata): string
    {
        $encoded = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            // Fall back to a safe placeholder rather than losing the log entry
            $encoded = json_encode(['_encode_error' => json_last_error_msg()]);
        }

        return $encoded;
    }

    /**
     * Return a consistent UTC timestamp string for the current instant.
     * Centralised here so tests can rely on a predictable format.
     */
    public function now(): string
    {
        return now()->toDateTimeString();
    }
}
