import { NextResponse } from "next/server";
import { runPhp } from "@/lib/php-bridge";

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const account = searchParams.get("account") || "general";
  const serial = searchParams.get("serial") || "";
  const { data, debug } = runPhp<Record<string, unknown>>("serial-eligible.php", [account, serial]);
  return NextResponse.json({ ...data, _debug: debug });
}
