"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useParams } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useAccount } from "@/lib/account-context";
import { ApiResponseViewer } from "@/components/api-response-viewer";
import type { ExchangeProject, Serial, DebugInfo } from "@/lib/types";

interface ProjectDetailRow {
  label: string;
  value: string | undefined | null;
}

function InfoRow({ label, value }: ProjectDetailRow) {
  if (!value) return null;
  return (
    <div className="flex gap-2 py-1 border-b border-border last:border-0">
      <span className="text-sm text-muted-foreground w-32 shrink-0">{label}</span>
      <span className="text-sm">{value}</span>
    </div>
  );
}

export default function ProjectDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { account } = useAccount();

  const [project, setProject] = useState<ExchangeProject | null>(null);
  const [serials, setSerials] = useState<Serial[]>([]);
  const [debug, setDebug] = useState<DebugInfo | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    Promise.all([
      fetch(`/api/projects/${id}?account=${account}`).then((r) => r.json()),
      fetch(`/api/positions?account=${account}`).then((r) => r.json()),
    ]).then(([projData, posData]) => {
      setProject(projData.project ?? null);
      setDebug(projData._debug ?? null);

      const allSerials: Serial[] = [];
      for (const b of posData.balance ?? []) {
        for (const s of b.serials ?? []) {
          const projId = s.serial.split("_")[1];
          if (projId === String(id)) {
            allSerials.push(s);
          }
        }
      }
      setSerials(allSerials);
      setLoading(false);
    });
  }, [id, account]);

  if (loading) {
    return (
      <main className="mx-auto max-w-4xl p-6">
        <p className="text-sm text-muted-foreground">Loading project...</p>
      </main>
    );
  }

  if (!project) {
    return (
      <main className="mx-auto max-w-4xl p-6">
        <Link href="/" className={cn(buttonVariants({ variant: "outline", size: "sm" }))}>
          ← Back
        </Link>
        <p className="mt-4 text-sm text-muted-foreground">Project not found.</p>
      </main>
    );
  }

  return (
    <main className="mx-auto max-w-4xl p-6 space-y-6">
      <div className="flex items-center gap-3">
        <Link href="/" className={cn(buttonVariants({ variant: "outline", size: "sm" }))}>
          ← Back
        </Link>
        <h1 className="text-2xl font-bold">{project.name}</h1>
        <Badge variant="outline">{project.stage}</Badge>
      </div>

      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base">Project Info</CardTitle>
        </CardHeader>
        <CardContent>
          <InfoRow label="Standard" value={project.standard} />
          <InfoRow label="Credit Type" value={project.credits_type} />
          <InfoRow label="Methodology" value={project.quantification_method} />
          <InfoRow label="Validator" value={project.validator} />
          <InfoRow label="Verifier" value={project.verifier} />
          <InfoRow
            label="Location"
            value={project.locations?.[0]
              ? [project.locations[0].city, project.locations[0].region, project.locations[0].country]
                  .filter(Boolean)
                  .join(", ")
              : undefined}
          />
          <InfoRow label="Owner" value={project.owner} />
          <InfoRow label="Developer" value={project.developer} />
          {project.description && (
            <div className="mt-3 pt-3 border-t border-border">
              <p className="text-sm text-muted-foreground mb-1">Description</p>
              <p className="text-sm">{project.description}</p>
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base">
            Credit Serials
            <span className="ml-2 text-sm font-normal text-muted-foreground">
              ({serials.length} serials, {serials.reduce((s, x) => s + x.quantity, 0).toLocaleString()} available)
            </span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          {serials.length === 0 ? (
            <p className="text-sm text-muted-foreground">No serials found for this account.</p>
          ) : (
            <div className="space-y-2">
              {serials.map((s) => {
                const parts = s.serial.split("_");
                const vintage = parts[parts.length - 1];
                return (
                  <div
                    key={s.serial}
                    className="flex items-center justify-between rounded-lg border border-border px-3 py-2"
                  >
                    <div>
                      <p className="font-mono text-sm">{s.serial}</p>
                      <p className="text-xs text-muted-foreground">
                        Vintage: {vintage} &middot; Available: {s.quantity.toLocaleString()}
                        {s.quantity_lock > 0 && ` · Locked: ${s.quantity_lock.toLocaleString()}`}
                      </p>
                    </div>
                    <Link
                      href={`/retire?serial=${encodeURIComponent(s.serial)}`}
                      className={cn(buttonVariants({ variant: "outline", size: "sm" }))}
                    >
                      Retire Credits
                    </Link>
                  </div>
                );
              })}
            </div>
          )}
        </CardContent>
      </Card>

      <ApiResponseViewer debug={debug} />
    </main>
  );
}
