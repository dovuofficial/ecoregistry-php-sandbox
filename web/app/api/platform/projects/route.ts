import { NextResponse } from "next/server";
import { runPhp } from "@/lib/php-bridge";

export async function GET() {
  const { data, debug } = runPhp<Record<string, unknown>>("platform-projects.php");
  return NextResponse.json({ ...data, _debug: debug });
}
