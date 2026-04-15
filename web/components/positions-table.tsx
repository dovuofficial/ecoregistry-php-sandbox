"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useAccount } from "@/lib/account-context";
import { ApiResponseViewer } from "./api-response-viewer";
import type { Serial, DebugInfo } from "@/lib/types";

export function PositionsTable() {
  const { account } = useAccount();
  const [serials, setSerials] = useState<Serial[]>([]);
  const [debug, setDebug] = useState<DebugInfo | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    fetch(`/api/positions?account=${account}`)
      .then((r) => r.json())
      .then((data) => {
        const all: Serial[] = [];
        for (const b of data.balance ?? []) {
          all.push(...(b.serials ?? []));
        }
        setSerials(all);
        setDebug(data._debug ?? null);
        setLoading(false);
      });
  }, [account]);

  return (
    <div>
      <h2 className="text-xl font-semibold">Positions</h2>
      <p className="mb-4 text-sm text-muted-foreground">
        {loading
          ? "Loading..."
          : serials.length > 0
            ? `${serials.length} serials for ${account} account`
            : `No serials found for ${account} account — the exchange admin API may not expose this account's positions`}
      </p>
      {serials.length === 0 && !loading && (
        <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
          {account === "user"
            ? "The \"Dovu test 1\" account's credits (CDC_4, CDC_35) are visible in the EcoRegistry UI but not through the exchange admin positions API. This is the same x-api-key auth limitation that affects retirement for this account."
            : "No positions found."}
        </div>
      )}
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Serial</TableHead>
            <TableHead className="text-right">Available</TableHead>
            <TableHead className="text-right">Locked</TableHead>
            <TableHead></TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {serials.map((s) => (
            <TableRow key={s.serial}>
              <TableCell className="font-mono text-sm">{s.serial}</TableCell>
              <TableCell className="text-right font-bold">
                {s.quantity.toLocaleString()}
              </TableCell>
              <TableCell className="text-right">
                {s.quantity_lock.toLocaleString()}
              </TableCell>
              <TableCell>
                <Link
                  href={`/retire?serial=${encodeURIComponent(s.serial)}`}
                  className={cn(buttonVariants({ size: "sm", variant: "outline" }))}
                >
                  Retire
                </Link>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <div className="mt-4">
        <ApiResponseViewer debug={debug} />
      </div>
    </div>
  );
}
