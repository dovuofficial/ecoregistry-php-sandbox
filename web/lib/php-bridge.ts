import { execSync } from "child_process";
import path from "path";
import type { DebugInfo } from "./types";

const PHP_DIR = path.resolve(process.cwd(), "php");
const PROJECT_ROOT = path.resolve(process.cwd(), "..");

interface PhpResult<T> {
  data: T;
  debug: DebugInfo;
}

export function runPhp<T = unknown>(
  script: string,
  args: string[] = [],
  stdin?: string
): PhpResult<T> {
  const scriptPath = path.join(PHP_DIR, script);
  const cmd = `php ${scriptPath} ${args.map((a) => JSON.stringify(a)).join(" ")}`;
  const start = Date.now();

  let stdout = "";
  let stderr = "";
  let exitCode: number | null = 0;

  try {
    stdout = execSync(cmd, {
      cwd: PROJECT_ROOT,
      input: stdin,
      encoding: "utf-8",
      timeout: 30000,
      stdio: ["pipe", "pipe", "pipe"],
    });
  } catch (err: unknown) {
    const execErr = err as { stdout?: string; stderr?: string; status?: number };
    stdout = execErr.stdout ?? "";
    stderr = execErr.stderr ?? "";
    exitCode = execErr.status ?? 1;
  }

  const durationMs = Date.now() - start;
  const debug: DebugInfo = {
    script,
    args,
    stdout: stdout.slice(0, 5000),
    stderr: stderr.slice(0, 2000),
    exitCode,
    durationMs,
  };

  let data: T;
  try {
    data = JSON.parse(stdout);
  } catch {
    data = { error: "Failed to parse PHP output", raw: stdout } as T;
  }

  return { data, debug };
}
