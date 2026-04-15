import { NextResponse } from "next/server";
import { runPhp } from "@/lib/php-bridge";

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const account = searchParams.get("account") || "general";
  const { data, debug } = runPhp<Record<string, unknown>>("exchange-projects.php", [account]);
  return NextResponse.json({ ...data, _debug: debug });
}
