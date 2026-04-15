"use client";

import { useState } from "react";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";

interface Props {
  debug: {
    script: string;
    args: string[];
    stdout: string;
    stderr: string;
    exitCode: number | null;
    durationMs: number;
  } | null;
}

export function ApiResponseViewer({ debug }: Props) {
  const [open, setOpen] = useState(false);
  if (!debug) return null;

  return (
    <Collapsible open={open} onOpenChange={setOpen}>
      <CollapsibleTrigger className="text-xs text-muted-foreground hover:text-foreground">
        {open ? "▾" : "▸"} Raw API Response ({debug.durationMs}ms)
      </CollapsibleTrigger>
      <CollapsibleContent>
        <pre className="mt-2 max-h-60 overflow-auto rounded-lg bg-slate-800 p-4 text-xs text-slate-200">
          <span className="text-slate-400">
            $ php {debug.script} {debug.args.join(" ")}
          </span>
          {"\n\n"}
          {debug.stdout}
          {debug.stderr && (
            <>
              {"\n"}
              <span className="text-red-400">{debug.stderr}</span>
            </>
          )}
        </pre>
      </CollapsibleContent>
    </Collapsible>
  );
}
