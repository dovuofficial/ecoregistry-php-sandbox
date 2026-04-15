"use client";

import { useEffect, useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button, buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { ApiResponseViewer } from "./api-response-viewer";
import type { DebugInfo } from "@/lib/types";

interface PlatformProject {
  id: number | string;
  name?: string;
  standard?: string;
  locations?: Array<{ country?: string }>;
  [key: string]: unknown;
}

export function ProductionPreview() {
  const [projects, setProjects] = useState<PlatformProject[]>([]);
  const [debug, setDebug] = useState<DebugInfo | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [selectedProject, setSelectedProject] = useState<PlatformProject | null>(null);
  const [detailData, setDetailData] = useState<unknown>(null);
  const [detailDebug, setDetailDebug] = useState<DebugInfo | null>(null);
  const [loadingDetail, setLoadingDetail] = useState(false);

  useEffect(() => {
    fetch("/api/platform/projects")
      .then((r) => r.json())
      .then((data) => {
        if (data.error || data.message) {
          setError(data.error ?? data.message ?? "Unknown error from platform API");
        } else {
          const list: PlatformProject[] = data.projects ?? data.data ?? [];
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
        setDetailData(data);
        setDetailDebug(data._debug ?? null);
        setLoadingDetail(false);
      });
  }

  function handleBack() {
    setSelectedProject(null);
    setDetailData(null);
    setDetailDebug(null);
  }

  if (selectedProject) {
    return (
      <div className="space-y-4">
        <div className="flex items-center gap-3">
          <Button variant="outline" size="sm" onClick={handleBack}>
            ← Back to projects
          </Button>
          <h2 className="text-lg font-semibold">{selectedProject.name ?? `Project ${selectedProject.id}`}</h2>
        </div>

        {loadingDetail ? (
          <p className="text-sm text-muted-foreground">Loading project details...</p>
        ) : (
          <pre className="rounded-lg bg-slate-800 p-4 text-xs text-slate-200 overflow-auto max-h-[60vh]">
            {JSON.stringify(detailData, null, 2)}
          </pre>
        )}

        <ApiResponseViewer debug={detailDebug} />
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-3">
        <h2 className="text-xl font-semibold">Production Platform Projects</h2>
        <Badge className="bg-amber-500 text-white hover:bg-amber-600">PRODUCTION</Badge>
        <span className="text-xs text-muted-foreground">Read-only</span>
      </div>

      {loading && <p className="text-sm text-muted-foreground">Loading production projects...</p>}

      {error && (
        <Card className="border-amber-300 bg-amber-50 dark:bg-amber-950/20">
          <CardHeader className="pb-2">
            <CardTitle className="text-base text-amber-700 dark:text-amber-400">
              Platform API Unavailable
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              {error}
            </p>
            <p className="mt-2 text-xs text-muted-foreground">
              Ensure PLATFORM_TOKEN is configured in your .env file.
            </p>
          </CardContent>
        </Card>
      )}

      {!loading && !error && projects.length === 0 && (
        <p className="text-sm text-muted-foreground">No projects found on the production platform.</p>
      )}

      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        {projects.map((p) => (
          <Card key={p.id}>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm">{p.name ?? `Project ${p.id}`}</CardTitle>
              <p className="text-xs text-muted-foreground">
                {[p.standard, p.locations?.[0]?.country].filter(Boolean).join(" · ") || "No location"}
              </p>
            </CardHeader>
            <CardContent>
              <Button
                variant="outline"
                size="sm"
                onClick={() => handleViewDetails(p)}
              >
                View Full Details →
              </Button>
            </CardContent>
          </Card>
        ))}
      </div>

      <ApiResponseViewer debug={debug} />
    </div>
  );
}
