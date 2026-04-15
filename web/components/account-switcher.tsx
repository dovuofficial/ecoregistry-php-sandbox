"use client";

import { useAccount } from "@/lib/account-context";
import type { Account } from "@/lib/types";

export function AccountSwitcher() {
  const { account, setAccount, companyId } = useAccount();
  return (
    <div className="flex items-center gap-2">
      <span className="text-sm text-muted-foreground">Account:</span>
      <select
        value={account}
        onChange={(e) => setAccount(e.target.value as Account)}
        className="rounded-md border border-slate-600 bg-slate-800 px-2 py-1 text-sm text-white"
      >
        <option value="general">general account (ECOxC_747917085_1)</option>
        <option value="user">Dovu test 1 (ECOxC_1159126991_19)</option>
      </select>
      <span className="text-xs text-slate-400">{companyId}</span>
    </div>
  );
}
