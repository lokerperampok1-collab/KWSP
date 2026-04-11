<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/schema.php";

/**
 * Wallet helpers.
 *
 * Data model:
 * - wallets: existence/uniqueness (user_id,currency)
 * - wallet_transactions: ledger of movements
 *
 * IMPORTANT (withdraw hold):
 * - Incoming funds count only when status='approved'
 * - Withdraw reduces balance when status IN ('pending','approved')
 *   (so balance is held immediately, and returns automatically on reject/void)
 */

function ensure_wallet(PDO $pdo, int $userId, string $currency = 'RM'): void {
  ensure_wallet_schema($pdo);

  // Create the wallet row if missing (unique constraint handles duplicates).
  $stmt = $pdo->prepare("INSERT IGNORE INTO wallets (user_id, currency) VALUES (?, ?)");
  $stmt->execute([$userId, $currency]);
}

function wallet_balance(PDO $pdo, int $userId, string $currency = 'RM'): string {
  ensure_wallet_schema($pdo);

  $sql = "
    SELECT COALESCE(SUM(
      CASE
        -- INCOME: only approved
        WHEN type IN ('deposit','profit','adjust','transfer_in') AND status='approved' THEN amount

        -- EXPENSE: approved only
        WHEN type IN ('investment','transfer_out') AND status='approved' THEN -amount

        -- WITHDRAW: pending + approved (hold immediately)
        WHEN type='withdraw' AND status IN ('pending','approved') THEN -amount

        ELSE 0
      END
    ), 0) AS bal
    FROM wallet_transactions
    WHERE user_id = ? AND currency = ?
      AND status IN ('approved','pending','rejected','void')
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$userId, $currency]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

  return number_format((float)($row["bal"] ?? 0), 2, ".", "");
}

function money_ok(string $amount): bool {
  // angka >= 0.01, max 2 desimal
  if (!preg_match('/^\d+(\.\d{1,2})?$/', $amount)) return false;
  return (float)$amount >= 0.01;
}
