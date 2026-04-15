"use client";

import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";

interface DebugEndpoint {
  method: "GET" | "POST";
  path: string;
  label: string;
}

const ENDPOINTS: DebugEndpoint[] = [
  { method: "POST", path: "/api-exchange-v2/v2/retirement", label: "POST /api-exchange-v2/v2/retirement" },
  { method: "GET", path: "/api-exchange-v2/v2/get-all-positions", label: "GET /api-exchange-v2/v2/get-all-positions" },
  { method: "POST", path: "/api-exchange-v2/v2/lock-serial", label: "POST /api-exchange-v2/v2/lock-serial" },
  { method: "POST", path: "/api-exchange-v2/v2/unlock-serial", label: "POST /api-exchange-v2/v2/unlock-serial" },
  { method: "POST", path: "/api-exchange-v2/v2/transfer-between", label: "POST /api-exchange-v2/v2/transfer-between" },
  { method: "POST", path: "/api-exchange-v2/v2/serial-eligible", label: "POST /api-exchange-v2/v2/serial-eligible" },
];

const EXAMPLE_RETIREMENT_BODY = JSON.stringify(
  {
    serial: "ECOxP_12345_2023",
    quantity: 1,
    reasonId: 1,
    observation: "Test retirement",
    endUser: {
      name: "Test User",
      countryId: 170,
      documentTypeId: 1,
      documentNumber: "12345678",
    },
  },
  null,
  2
);

interface DebugResponse {
  data: unknown;
  stderr: string;
  durationMs: number;
}

export function DebugPanel() {
  const [adminKey, setAdminKey] = useState("");
  const [apiKey, setApiKey] = useState("");
  const [selectedEndpoint, setSelectedEndpoint] = useState(ENDPOINTS[0].path);
  const [requestBody, setRequestBody] = useState(EXAMPLE_RETIREMENT_BODY);
  const [loading, setLoading] = useState(false);
  const [response, setResponse] = useState<DebugResponse | null>(null);

  const endpoint = ENDPOINTS.find((e) => e.path === selectedEndpoint) ?? ENDPOINTS[0];
  const isPost = endpoint.method === "POST";

  async function handleSend() {
    setLoading(true);
    setResponse(null);

    const headers: Record<string, string> = {};
    if (adminKey) headers["x-api-key-admin"] = adminKey;
    if (apiKey) headers["x-api-key"] = apiKey;

    let parsedBody = null;
    if (isPost) {
      try {
        parsedBody = JSON.parse(requestBody);
      } catch {
        parsedBody = requestBody;
      }
    }

    const res = await fetch("/api/debug", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        endpoint: selectedEndpoint,
        method: endpoint.method,
        headers,
        requestBody: parsedBody,
      }),
    });

    const data: DebugResponse = await res.json();
    setResponse(data);
    setLoading(false);
  }

  return (
    <div className="space-y-4">
      <div>
        <h2 className="text-xl font-semibold">Auth Debug</h2>
        <p className="text-sm text-muted-foreground">
          Test different auth configurations against the EcoRegistry Exchange API.
        </p>
      </div>

      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base">Request Configuration</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1">
              <label className="text-sm font-medium">x-api-key-admin</label>
              <Input
                value={adminKey}
                onChange={(e) => setAdminKey(e.target.value)}
                placeholder="(auto from exchange auth)"
              />
            </div>
            <div className="space-y-1">
              <label className="text-sm font-medium">x-api-key</label>
              <Input
                value={apiKey}
                onChange={(e) => setApiKey(e.target.value)}
                placeholder="Paste Token API Exchanges here"
              />
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-sm font-medium">Endpoint</label>
            <Select
              value={selectedEndpoint}
              onValueChange={(v) => { if (v) setSelectedEndpoint(v); }}
            >
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Select endpoint..." />
              </SelectTrigger>
              <SelectContent>
                {ENDPOINTS.map((e) => (
                  <SelectItem key={e.path} value={e.path}>
                    {e.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {isPost && (
            <div className="space-y-1">
              <label className="text-sm font-medium">Request Body (JSON)</label>
              <Textarea
                value={requestBody}
                onChange={(e) => setRequestBody(e.target.value)}
                className="font-mono text-xs"
                rows={10}
              />
            </div>
          )}

          <Button onClick={handleSend} disabled={loading}>
            {loading ? "Sending..." : "Send Request"}
          </Button>
        </CardContent>
      </Card>

      {response && (
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">
              Response
              <span className="ml-2 text-sm font-normal text-muted-foreground">
                ({response.durationMs}ms)
              </span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <pre className="rounded-lg bg-slate-800 p-4 text-xs text-slate-200 overflow-auto max-h-96">
              {JSON.stringify(response.data, null, 2)}
              {response.stderr && (
                "\n\n// stderr:\n" + response.stderr
              )}
            </pre>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
