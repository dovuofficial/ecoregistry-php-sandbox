import { NextResponse } from "next/server";
import { runPhp } from "@/lib/php-bridge";

export async function GET(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const { searchParams } = new URL(request.url);
  const account = searchParams.get("account") || "general";
  const { data, debug } = runPhp<Record<string, unknown>>("exchange-project.php", [account, id]);
  return NextResponse.json({ ...data, _debug: debug });
}
