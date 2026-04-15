"use client";

import { createContext, useContext, useState, type ReactNode } from "react";
import type { Account } from "./types";

interface AccountContextValue {
  account: Account;
  setAccount: (a: Account) => void;
  label: string;
  companyId: string;
}

const ACCOUNTS: Record<Account, { label: string; companyId: string }> = {
  general: { label: "general account", companyId: "ECOxC_747917085_1" },
  user: { label: "Dovu test 1", companyId: "ECOxC_1159126991_19" },
};

const AccountContext = createContext<AccountContextValue | null>(null);

export function AccountProvider({ children }: { children: ReactNode }) {
  const [account, setAccount] = useState<Account>("general");
  const { label, companyId } = ACCOUNTS[account];
  return (
    <AccountContext.Provider value={{ account, setAccount, label, companyId }}>
      {children}
    </AccountContext.Provider>
  );
}

export function useAccount() {
  const ctx = useContext(AccountContext);
  if (!ctx) throw new Error("useAccount must be inside AccountProvider");
  return ctx;
}
