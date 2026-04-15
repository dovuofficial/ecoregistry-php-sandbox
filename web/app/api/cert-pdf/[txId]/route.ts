import { NextResponse } from "next/server";
import { runPhp } from "@/lib/php-bridge";

export async function GET(
  request: Request,
  { params }: { params: Promise<{ txId: string }> }
) {
  const { txId } = await params;
  const { data, debug } = runPhp<Record<string, unknown>>("cert-pdf.php", ["general", txId]);
  return NextResponse.json({ ...data, _debug: debug });
}
