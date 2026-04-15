"use client";

import { useEffect, useState } from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";

interface TxRecord {
  transactionId: number;
  serial: string;
  quantity: number;
  date: string;
  account: string;
  observation: string;
  urlPDF?: string;
  timestamp: string;
}

export function TxHistoryTable() {
  const [history, setHistory] = useState<TxRecord[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch("/api/tx-history")
      .then((r) => r.json())
      .then((data) => {
        setHistory(Array.isArray(data) ? data : []);
        setLoading(false);
      });
  }, []);

  return (
    <div>
      <h2 className="text-xl font-semibold">Transaction History</h2>
      <p className="mb-4 text-sm text-muted-foreground">
        {loading
          ? "Loading..."
          : `${history.length} retirement${history.length !== 1 ? "s" : ""} recorded this session`}
      </p>

      {history.length === 0 && !loading ? (
        <p className="text-sm text-muted-foreground py-8 text-center">
          No retirements recorded yet. Execute a retirement to see it here.
        </p>
      ) : (
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Tx ID</TableHead>
              <TableHead>Serial</TableHead>
              <TableHead className="text-right">Qty</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Account</TableHead>
              <TableHead>Observation</TableHead>
              <TableHead>Certificate</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {history.map((tx) => (
              <TableRow key={tx.transactionId}>
                <TableCell className="font-mono font-bold">
                  {tx.transactionId}
                </TableCell>
                <TableCell className="font-mono text-xs">
                  {tx.serial}
                </TableCell>
                <TableCell className="text-right font-bold">
                  {tx.quantity}
                </TableCell>
                <TableCell className="text-sm">{tx.date}</TableCell>
                <TableCell>
                  <Badge variant="outline" className="text-xs">
                    {tx.account}
                  </Badge>
                </TableCell>
                <TableCell className="text-sm text-muted-foreground max-w-32 truncate">
                  {tx.observation || "-"}
                </TableCell>
                <TableCell>
                  {tx.urlPDF ? (
                    <a
                      href={tx.urlPDF}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-xs text-blue-600 hover:underline"
                    >
                      View PDF ↗
                    </a>
                  ) : (
                    <span className="text-xs text-muted-foreground">-</span>
                  )}
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      )}
    </div>
  );
}
