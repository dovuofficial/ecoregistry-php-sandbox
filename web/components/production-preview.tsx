"use client";

import { useEffect, useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { ApiResponseViewer } from "./api-response-viewer";
import type { DebugInfo } from "@/lib/types";

interface PlatformProject {
  id: number;
  code?: string;
  name?: string;
  description?: string;
  standard?: string;
  stage?: string;
  credits_type?: string;
  evaluation_criteria?: string;
  quantification_method?: string;
  owner?: string;
  developer?: string;
  validator?: string;
  verifier?: string;
  is_public?: boolean;
  locations?: Array<{ country?: string; region?: string; city?: string }>;
  serials?: Array<{
    serial?: string;
    issued_quantity?: number;
    quantity_certified?: number;
    vintage_of_credits?: string;
    year?: number;
    issuance_date?: string;
    is_buffer?: boolean;
    global_goals?: Array<{ code: number; description: string }>;
    elegible?: Array<{ code: string; description: string }>;
  }>;
  sector?: number[];
  sdg_pilar?: number[];
  annualEstimated?: number;
  [key: string]: unknown;
}

export function ProductionPreview() {
  const [projects, setProjects] = useState<PlatformProject[]>([]);
  const [debug, setDebug] = useState<DebugInfo | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [search, setSearch] = useState("");

  const [selectedProject, setSelectedProject] = useState<PlatformProject | null>(null);
  const [detailData, setDetailData] = useState<PlatformProject | null>(null);
  const [detailDebug, setDetailDebug] = useState<DebugInfo | null>(null);
  const [loadingDetail, setLoadingDetail] = useState(false);

  useEffect(() => {
    fetch("/api/platform/projects")
      .then((r) => r.json())
      .then((data) => {
        if (data.error) {
          setError(data.error);
        } else {
          const list: PlatformProject[] = data.project ?? data.projects ?? [];
          setProjects(list);
        }
        setDebug(data._debug ?? null);
        setLoading(false);
      })
      .catch((err) => {
        setError(String(err));
        setLoading(false);
      });
  }, []);

  function handleViewDetails(project: PlatformProject) {
    setSelectedProject(project);
    setLoadingDetail(true);
    setDetailData(null);
    fetch(`/api/platform/projects/${project.id}`)
      .then((r) => r.json())
      .then((data) => {
        const detail = data.project ?? data;
        // Merge serials from list data if detail doesn't have them
        if ((!detail.serials || detail.serials.length === 0) && project.serials) {
          detail.serials = project.serials;
        }
        setDetailData(detail);
        setDetailDebug(data._debug ?? null);
        setLoadingDetail(false);
      });
  }

  const filtered = projects.filter((p) => {
    if (!search) return true;
    const s = search.toLowerCase();
    return (
      (p.name ?? "").toLowerCase().includes(s) ||
      (p.code ?? "").toLowerCase().includes(s) ||
      (p.standard ?? "").toLowerCase().includes(s) ||
      (p.locations?.[0]?.country ?? "").toLowerCase().includes(s)
    );
  });

  // Detail view
  if (selectedProject) {
    const p = detailData ?? selectedProject;
    const serials = p.serials ?? [];
    const totalIssued = serials.reduce(
      (sum, s) => sum + (s.issued_quantity ?? s.quantity_certified ?? 0),
      0
    );
    const sdgs = serials[0]?.global_goals ?? [];
    const vintageYears = [...new Set(serials.map((s) => s.year).filter(Boolean))].sort();

    return (
      <div className="space-y-4">
        <div className="flex items-center gap-3">
          <Button
            variant="outline"
            size="sm"
            onClick={() => {
              setSelectedProject(null);
              setDetailData(null);
            }}
          >
            ← Back
          </Button>
          <Badge className="bg-amber-500 text-white hover:bg-amber-600">
            PRODUCTION
          </Badge>
        </div>

        {loadingDetail ? (
          <p className="text-sm text-muted-foreground">Loading...</p>
        ) : (
          <>
            <div>
              <h2 className="text-xl font-semibold">{p.name ?? `Project ${p.id}`}</h2>
              <p className="text-sm text-muted-foreground">
                {[p.code, p.standard, p.locations?.[0]?.country]
                  .filter(Boolean)
                  .join(" · ")}
              </p>
            </div>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
              {/* Main info */}
              <div className="md:col-span-2 space-y-4">
                <Card>
                  <CardHeader>
                    <CardTitle className="text-sm uppercase text-muted-foreground">
                      Project Information
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-1">
                    {[
                      ["Code", p.code],
                      ["Standard", p.standard],
                      ["Stage", p.stage],
                      ["Credit Type", p.credits_type],
                      ["Evaluation Criteria", p.evaluation_criteria],
                      ["Quantification Method", p.quantification_method],
                      ["Validator", p.validator],
                      ["Verifier", p.verifier],
                      ["Owner", p.owner],
                      ["Developer", p.developer],
                      [
                        "Location",
                        p.locations
                          ?.map((l) =>
                            [l.country, l.region, l.city]
                              .filter(Boolean)
                              .join(", ")
                          )
                          .join(" | "),
                      ],
                    ]
                      .filter(([, v]) => v)
                      .map(([k, v]) => (
                        <div
                          key={k}
                          className="flex gap-2 py-1.5 border-b border-border last:border-0"
                        >
                          <span className="text-sm text-muted-foreground w-40 shrink-0">
                            {k}
                          </span>
                          <span className="text-sm">{v}</span>
                        </div>
                      ))}
                  </CardContent>
                </Card>

                {p.description && (
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-sm uppercase text-muted-foreground">
                        Description
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <p className="text-sm text-muted-foreground leading-relaxed">
                        {String(p.description).slice(0, 1000)}
                      </p>
                    </CardContent>
                  </Card>
                )}
              </div>

              {/* Serials & stats */}
              <div className="space-y-4">
                <Card>
                  <CardHeader>
                    <CardTitle className="text-sm uppercase text-muted-foreground">
                      Summary
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <div className="text-2xl font-bold">
                          {totalIssued.toLocaleString()}
                        </div>
                        <div className="text-xs uppercase text-muted-foreground">
                          Total Issued
                        </div>
                      </div>
                      <div>
                        <div className="text-2xl font-bold">{serials.length}</div>
                        <div className="text-xs uppercase text-muted-foreground">
                          Serials
                        </div>
                      </div>
                      <div>
                        <div className="text-2xl font-bold">
                          {vintageYears.length > 1
                            ? `${vintageYears[0]}-${vintageYears[vintageYears.length - 1]}`
                            : vintageYears[0] ?? "-"}
                        </div>
                        <div className="text-xs uppercase text-muted-foreground">
                          Vintages
                        </div>
                      </div>
                      {p.annualEstimated ? (
                        <div>
                          <div className="text-2xl font-bold">
                            {Number(p.annualEstimated).toLocaleString()}
                          </div>
                          <div className="text-xs uppercase text-muted-foreground">
                            Annual Est.
                          </div>
                        </div>
                      ) : null}
                    </div>
                  </CardContent>
                </Card>

                {sdgs.length > 0 && (
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-sm uppercase text-muted-foreground">
                        Sustainable Development Goals
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="flex flex-wrap gap-1.5">
                        {sdgs.map((g) => (
                          <Badge key={g.code} variant="outline" className="text-xs">
                            SDG {g.code}: {g.description}
                          </Badge>
                        ))}
                      </div>
                    </CardContent>
                  </Card>
                )}

                {serials.length > 0 && (
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-sm uppercase text-muted-foreground">
                        Credit Serials ({serials.length})
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2 max-h-96 overflow-auto">
                      {serials.map((s, i) => (
                        <div
                          key={i}
                          className="rounded-lg bg-slate-50 dark:bg-slate-900 p-3"
                        >
                          <code className="text-xs text-blue-600 break-all">
                            {s.serial}
                          </code>
                          <div className="mt-1 flex justify-between text-sm">
                            <span>
                              Issued:{" "}
                              <strong>
                                {(s.issued_quantity ?? s.quantity_certified ?? 0).toLocaleString()}
                              </strong>
                            </span>
                            <span className="text-muted-foreground">
                              {s.vintage_of_credits ?? s.year ?? "-"}
                            </span>
                          </div>
                          {s.is_buffer && (
                            <Badge variant="outline" className="mt-1 text-xs">
                              Buffer
                            </Badge>
                          )}
                        </div>
                      ))}
                    </CardContent>
                  </Card>
                )}
              </div>
            </div>

            <ApiResponseViewer debug={detailDebug} />
          </>
        )}
      </div>
    );
  }

  // List view
  return (
    <div className="space-y-4">
      <div className="flex items-center gap-3">
        <h2 className="text-xl font-semibold">Production Projects</h2>
        <Badge className="bg-amber-500 text-white hover:bg-amber-600">
          PRODUCTION
        </Badge>
        <span className="text-xs text-muted-foreground">
          Read-only · {projects.length} projects available for DOVU Market
          ingestion
        </span>
      </div>

      {loading && (
        <p className="text-sm text-muted-foreground">
          Loading production projects...
        </p>
      )}

      {error && (
        <Card className="border-amber-300 bg-amber-50">
          <CardContent className="pt-6">
            <p className="text-sm text-amber-800">{error}</p>
            <p className="mt-1 text-xs text-muted-foreground">
              Ensure PLATFORM_TOKEN is set in .env
            </p>
          </CardContent>
        </Card>
      )}

      {!loading && !error && (
        <>
          <Input
            placeholder="Search projects by name, code, standard, or country..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="max-w-md"
          />

          <Table>
            <TableHeader>
              <TableRow>
                <TableHead className="w-16">ID</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Standard</TableHead>
                <TableHead>Country</TableHead>
                <TableHead>Stage</TableHead>
                <TableHead className="text-right">Serials</TableHead>
                <TableHead></TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filtered.slice(0, 50).map((p) => (
                <TableRow key={p.id}>
                  <TableCell className="font-mono text-xs">{p.id}</TableCell>
                  <TableCell className="font-medium">
                    {p.name ?? `Project ${p.id}`}
                  </TableCell>
                  <TableCell className="text-sm">
                    {p.standard ?? "-"}
                  </TableCell>
                  <TableCell className="text-sm">
                    {p.locations?.[0]?.country ?? "-"}
                  </TableCell>
                  <TableCell>
                    {p.stage && (
                      <Badge variant="outline" className="text-xs">
                        {p.stage}
                      </Badge>
                    )}
                  </TableCell>
                  <TableCell className="text-right">
                    {p.serials?.length ?? "-"}
                  </TableCell>
                  <TableCell>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handleViewDetails(p)}
                    >
                      Details →
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
          {filtered.length > 50 && (
            <p className="text-xs text-muted-foreground">
              Showing 50 of {filtered.length} projects. Use search to filter.
            </p>
          )}
        </>
      )}

      <ApiResponseViewer debug={debug} />
    </div>
  );
}
