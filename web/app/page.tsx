"use client";

import { useEffect, useState } from "react";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { useAccount } from "@/lib/account-context";
import { ProjectCard } from "@/components/project-card";
import { PositionsTable } from "@/components/positions-table";
import { ProductionPreview } from "@/components/production-preview";
import { DebugPanel } from "@/components/debug-panel";
import { TxHistoryTable } from "@/components/tx-history-table";
import { ApiResponseViewer } from "@/components/api-response-viewer";
import type { ExchangeProject, Serial, DebugInfo } from "@/lib/types";

export default function Dashboard() {
  const { account } = useAccount();
  const [projects, setProjects] = useState<ExchangeProject[]>([]);
  const [positions, setPositions] = useState<Record<string, Serial[]>>({});
  const [debug, setDebug] = useState<DebugInfo | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    Promise.all([
      fetch(`/api/projects?account=${account}`).then((r) => r.json()),
      fetch(`/api/positions?account=${account}`).then((r) => r.json()),
    ]).then(([projData, posData]) => {
      const proj = projData.projects;
      setProjects(Array.isArray(proj) ? proj : proj?.project ?? projData.project ?? []);
      setDebug(projData._debug ?? null);
      const balanceMap: Record<string, Serial[]> = {};
      for (const b of posData.balance ?? []) {
        for (const s of b.serials ?? []) {
          const projId = s.serial.split("_")[1];
          if (!balanceMap[projId]) balanceMap[projId] = [];
          balanceMap[projId].push(s);
        }
      }
      setPositions(balanceMap);
      setLoading(false);
    });
  }, [account]);

  return (
    <main className="mx-auto max-w-6xl p-6">
      <Tabs defaultValue="projects">
        <TabsList>
          <TabsTrigger value="projects">Projects</TabsTrigger>
          <TabsTrigger value="positions">Positions</TabsTrigger>
          <TabsTrigger value="history">History</TabsTrigger>
          <TabsTrigger value="production">Production Preview</TabsTrigger>
          <TabsTrigger value="debug" className="text-muted-foreground">
            Debug
          </TabsTrigger>
        </TabsList>

        <TabsContent value="projects" className="mt-6">
          <h2 className="text-xl font-semibold">Projects on Exchange</h2>
          <p className="mb-4 text-sm text-muted-foreground">
            {loading ? "Loading..." : `${projects.length} projects available`}
          </p>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            {projects.map((p) => (
              <ProjectCard
                key={p.id}
                project={p}
                serials={positions[String(p.id)] ?? []}
              />
            ))}
          </div>
          <div className="mt-4">
            <ApiResponseViewer debug={debug} />
          </div>
        </TabsContent>

        <TabsContent value="positions" className="mt-6">
          <PositionsTable />
        </TabsContent>

        <TabsContent value="history" className="mt-6">
          <TxHistoryTable />
        </TabsContent>

        <TabsContent value="production" className="mt-6">
          <ProductionPreview />
        </TabsContent>

        <TabsContent value="debug" className="mt-6">
          <DebugPanel />
        </TabsContent>
      </Tabs>
    </main>
  );
}
