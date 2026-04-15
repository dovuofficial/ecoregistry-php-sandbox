import { NextResponse } from "next/server";
import { runPhp } from "@/lib/php-bridge";

export async function GET(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const { data, debug } = runPhp<Record<string, unknown>>("platform-project.php", ["general", id]);
  return NextResponse.json({ ...data, _debug: debug });
}
