import { NextResponse } from "next/server";
import { runPhp } from "@/lib/php-bridge";

export async function POST(request: Request) {
  const { searchParams } = new URL(request.url);
  const account = searchParams.get("account") || "general";
  const body = await request.json();
  const { data, debug } = runPhp<Record<string, unknown>>("retire.php", [account], JSON.stringify(body));
  return NextResponse.json({ ...data, _debug: debug });
}
