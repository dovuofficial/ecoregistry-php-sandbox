"use client";

import { Suspense, useEffect, useState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button, buttonVariants } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { cn } from "@/lib/utils";
import { useAccount } from "@/lib/account-context";
import { ApiResponseViewer } from "@/components/api-response-viewer";
import type { Serial, EligibleReason, DebugInfo } from "@/lib/types";

const EXAMPLE_REASONS = [
  { id: 1, label: "Voluntary cancellation" },
  { id: 2, label: "Regulatory compliance" },
  { id: 3, label: "Corporate sustainability" },
];

const DOC_TYPES = [
  { id: 1, label: "Passport" },
  { id: 2, label: "National ID" },
  { id: 3, label: "Company Registration" },
];

interface RetirementResult {
  success: boolean;
  result?: {
    data: { serial: string; quantity: number; date: string };
    urlPDF: string;
    transactionId: number;
  };
  error?: string;
  detail?: unknown;
  _debug?: DebugInfo;
}

function RetireForm() {
  const searchParams = useSearchParams();
  const preSerial = searchParams.get("serial") ?? "";
  const { account } = useAccount();

  const [serials, setSerials] = useState<Serial[]>([]);
  const [selectedSerial, setSelectedSerial] = useState(preSerial);
  const [eligibleReasons, setEligibleReasons] = useState<EligibleReason[]>([]);
  const [eligibilityDebug, setEligibilityDebug] = useState<DebugInfo | null>(null);
  const [loadingSerials, setLoadingSerials] = useState(true);
  const [loadingEligibility, setLoadingEligibility] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [result, setResult] = useState<RetirementResult | null>(null);
  const [pdfUrl, setPdfUrl] = useState<string | null>(null);

  // Form fields
  const [quantity, setQuantity] = useState("1");
  const [reasonId, setReasonId] = useState("");
  const [endUserName, setEndUserName] = useState("");
  const [countryId, setCountryId] = useState("170"); // default Colombia
  const [docTypeId, setDocTypeId] = useState("1");
  const [docNumber, setDocNumber] = useState("");
  const [observation, setObservation] = useState("");

  useEffect(() => {
    setLoadingSerials(true);
    fetch(`/api/positions?account=${account}`)
      .then((r) => r.json())
      .then((data) => {
        const all: Serial[] = [];
        for (const b of data.balance ?? []) {
          all.push(...(b.serials ?? []));
        }
        setSerials(all);
        setLoadingSerials(false);
        if (!selectedSerial && all.length > 0) {
          setSelectedSerial(all[0].serial);
        }
      });
  }, [account]);

  useEffect(() => {
    if (!selectedSerial) return;
    setLoadingEligibility(true);
    setEligibleReasons([]);
    fetch(`/api/serial-eligible?account=${account}&serial=${encodeURIComponent(selectedSerial)}`)
      .then((r) => r.json())
      .then((data) => {
        setEligibleReasons(data.reasons ?? []);
        setEligibilityDebug(data._debug ?? null);
        setLoadingEligibility(false);
        if (data.reasons?.length > 0) {
          setReasonId(String(data.reasons[0].reasonUsingCarbonOffsetsId));
        }
      });
  }, [selectedSerial, account]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    setResult(null);
    setPdfUrl(null);

    const body = {
      serial: selectedSerial,
      quantity: Number(quantity),
      reasonId: Number(reasonId),
      observation,
      endUser: {
        name: endUserName,
        countryId: Number(countryId),
        documentTypeId: Number(docTypeId),
        documentNumber: docNumber,
      },
    };

    const res = await fetch(`/api/retire?account=${account}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });
    const data: RetirementResult = await res.json();
    setResult(data);

    if (data.success && data.result?.transactionId) {
      const txId = data.result.transactionId;
      const pdfRes = await fetch(`/api/cert-pdf/${txId}`);
      const pdfData = await pdfRes.json();
      if (pdfData.url) {
        setPdfUrl(pdfData.url);
      } else if (data.result.urlPDF) {
        setPdfUrl(data.result.urlPDF);
      }
    }

    setSubmitting(false);
  }

  const selectedSerialObj = serials.find((s) => s.serial === selectedSerial);

  return (
    <main className="mx-auto max-w-3xl p-6 space-y-6">
      <div className="flex items-center gap-3">
        <Link href="/" className={cn(buttonVariants({ variant: "outline", size: "sm" }))}>
          ← Back
        </Link>
        <h1 className="text-2xl font-bold">Retire Credits</h1>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">Select Serial</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {loadingSerials ? (
              <p className="text-sm text-muted-foreground">Loading serials...</p>
            ) : (
              <>
                <div className="space-y-1">
                  <label className="text-sm font-medium">Serial</label>
                  <Select value={selectedSerial} onValueChange={(v) => { if (v) setSelectedSerial(v); }}>
                    <SelectTrigger className="w-full">
                      <SelectValue placeholder="Select a serial..." />
                    </SelectTrigger>
                    <SelectContent>
                      {serials.map((s) => (
                        <SelectItem key={s.serial} value={s.serial}>
                          {s.serial} ({s.quantity.toLocaleString()} available)
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {selectedSerialObj && (
                  <p className="text-xs text-muted-foreground">
                    Available: {selectedSerialObj.quantity.toLocaleString()} ·
                    Locked: {selectedSerialObj.quantity_lock.toLocaleString()}
                  </p>
                )}

                <div className="space-y-1">
                  <label className="text-sm font-medium">Quantity</label>
                  <Input
                    type="number"
                    min="1"
                    max={selectedSerialObj?.quantity}
                    value={quantity}
                    onChange={(e) => setQuantity(e.target.value)}
                    required
                  />
                </div>

                <div className="space-y-1">
                  <label className="text-sm font-medium">Retirement Reason</label>
                  {loadingEligibility ? (
                    <p className="text-xs text-muted-foreground">Loading reasons...</p>
                  ) : (
                    <Select value={reasonId} onValueChange={(v) => { if (v) setReasonId(v); }}>
                      <SelectTrigger className="w-full">
                        <SelectValue placeholder="Select a reason..." />
                      </SelectTrigger>
                      <SelectContent>
                        {(eligibleReasons.length > 0 ? eligibleReasons : EXAMPLE_REASONS).map((r) => {
                          const rid = "reasonUsingCarbonOffsetsId" in r
                            ? r.reasonUsingCarbonOffsetsId
                            : (r as { id: number }).id;
                          const rdesc = "description" in r
                            ? r.description
                            : (r as { label: string }).label;
                          return (
                            <SelectItem key={rid} value={String(rid)}>
                              {rdesc}
                            </SelectItem>
                          );
                        })}
                      </SelectContent>
                    </Select>
                  )}
                </div>
              </>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">End User Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1">
              <label className="text-sm font-medium">End User Name</label>
              <Input
                value={endUserName}
                onChange={(e) => setEndUserName(e.target.value)}
                placeholder="Full name or company"
                required
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium">Country ID</label>
                <Input
                  type="number"
                  value={countryId}
                  onChange={(e) => setCountryId(e.target.value)}
                  placeholder="e.g. 170"
                  required
                />
              </div>

              <div className="space-y-1">
                <label className="text-sm font-medium">Doc Type</label>
                <Select value={docTypeId} onValueChange={(v) => { if (v) setDocTypeId(v); }}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select doc type..." />
                  </SelectTrigger>
                  <SelectContent>
                    {DOC_TYPES.map((d) => (
                      <SelectItem key={d.id} value={String(d.id)}>
                        {d.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium">Document Number</label>
              <Input
                value={docNumber}
                onChange={(e) => setDocNumber(e.target.value)}
                placeholder="Document number"
                required
              />
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium">Observation</label>
              <Textarea
                value={observation}
                onChange={(e) => setObservation(e.target.value)}
                placeholder="Optional notes about this retirement"
                rows={3}
              />
            </div>
          </CardContent>
        </Card>

        <Button
          type="submit"
          disabled={submitting || !selectedSerial || !reasonId}
          className="w-full"
        >
          {submitting ? "Executing Retirement..." : "Execute Retirement"}
        </Button>
      </form>

      {result && (
        <div className="space-y-4">
          {result.success && result.result ? (
            <Card className="border-green-500 bg-green-50 dark:bg-green-950/20">
              <CardHeader className="pb-2">
                <CardTitle className="text-base text-green-700 dark:text-green-400">
                  Retirement Successful
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <div className="grid grid-cols-3 gap-4">
                  <div>
                    <p className="text-xs text-muted-foreground">Transaction ID</p>
                    <p className="font-mono font-bold">{result.result.transactionId}</p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground">Quantity Retired</p>
                    <p className="font-bold">{result.result.data.quantity.toLocaleString()}</p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground">Date</p>
                    <p className="font-bold">{result.result.data.date}</p>
                  </div>
                </div>
                {(pdfUrl ?? result.result.urlPDF) && (
                  <a
                    href={pdfUrl ?? result.result.urlPDF}
                    target="_blank"
                    rel="noopener noreferrer"
                    className={cn(buttonVariants({ variant: "outline", size: "sm" }))}
                  >
                    View Certificate PDF →
                  </a>
                )}
              </CardContent>
            </Card>
          ) : (
            <Card className="border-red-500 bg-red-50 dark:bg-red-950/20">
              <CardHeader className="pb-2">
                <CardTitle className="text-base text-red-700 dark:text-red-400">
                  Retirement Failed
                </CardTitle>
              </CardHeader>
              <CardContent>
                <pre className="rounded-lg bg-slate-800 p-4 text-xs text-slate-200 overflow-auto max-h-60">
                  {JSON.stringify({ error: result.error, detail: result.detail }, null, 2)}
                </pre>
              </CardContent>
            </Card>
          )}

          <ApiResponseViewer debug={result._debug ?? null} />
        </div>
      )}

      {eligibilityDebug && !result && (
        <ApiResponseViewer debug={eligibilityDebug} />
      )}
    </main>
  );
}

export default function RetirePage() {
  return (
    <Suspense fallback={<main className="mx-auto max-w-3xl p-6"><p className="text-sm text-muted-foreground">Loading...</p></main>}>
      <RetireForm />
    </Suspense>
  );
}
