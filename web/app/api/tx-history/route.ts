import { NextResponse } from "next/server";
import { addTx, getTxHistory, type TxRecord } from "@/lib/tx-history";

export async function GET() {
  return NextResponse.json(getTxHistory());
}

export async function POST(request: Request) {
  const record: TxRecord = await request.json();
  addTx(record);
  return NextResponse.json({ ok: true });
}
