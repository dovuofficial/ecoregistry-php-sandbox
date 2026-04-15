import { AccountSwitcher } from "./account-switcher";
import { Badge } from "@/components/ui/badge";

export function NavBar() {
  return (
    <nav className="flex items-center justify-between bg-slate-900 px-6 py-3 text-white">
      <div className="flex items-center gap-3">
        <h1 className="text-base font-semibold">EcoRegistry Explorer</h1>
        <Badge className="bg-green-500 text-slate-900 hover:bg-green-500">
          UAT
        </Badge>
      </div>
      <AccountSwitcher />
    </nav>
  );
}
