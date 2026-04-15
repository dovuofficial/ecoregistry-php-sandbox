import { execSync } from "child_process";
import { NextResponse } from "next/server";
import path from "path";

export async function POST(request: Request) {
  const body = await request.json();
  const { endpoint, method, headers, requestBody } = body;

  const projectRoot = path.resolve(process.cwd(), "..");
  const envPath = path.join(projectRoot, ".env");

  const phpCode = `
<?php
require_once '${path.join(projectRoot, "vendor/autoload.php")}';
$env = parse_ini_file('${envPath}');
$client = new \\Ecoregistry\\Http\\ApiClient($env['UAT_BASE_URL']);
$headers = json_decode('${JSON.stringify(headers)}', true);
$body = json_decode('${JSON.stringify(requestBody)}', true);
$method = '${method}';
$path = '${endpoint}';
$response = $client->request($method, $path, [], $body, $headers);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
`;

  const start = Date.now();
  let stdout = "";
  let stderr = "";

  try {
    stdout = execSync(`php -r '${phpCode.replace(/'/g, "'\\''")}'`, {
      cwd: projectRoot,
      encoding: "utf-8",
      timeout: 30000,
      stdio: ["pipe", "pipe", "pipe"],
    });
  } catch (err: unknown) {
    const execErr = err as { stdout?: string; stderr?: string };
    stdout = execErr.stdout ?? "";
    stderr = execErr.stderr ?? "";
  }

  const durationMs = Date.now() - start;

  let data;
  try {
    data = JSON.parse(stdout);
  } catch {
    data = { raw: stdout };
  }

  return NextResponse.json({ data, stderr, durationMs });
}
