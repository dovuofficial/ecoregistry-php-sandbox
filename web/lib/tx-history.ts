import fs from "fs";
import path from "path";

const HISTORY_FILE = path.resolve(process.cwd(), "..", "tx-history.json");

export interface TxRecord {
  transactionId: number;
  serial: string;
  quantity: number;
  date: string;
  account: string;
  observation: string;
  urlPDF?: string;
  timestamp: string;
}

function readHistory(): TxRecord[] {
  try {
    const raw = fs.readFileSync(HISTORY_FILE, "utf-8");
    return JSON.parse(raw);
  } catch {
    return [];
  }
}

function writeHistory(records: TxRecord[]): void {
  fs.writeFileSync(HISTORY_FILE, JSON.stringify(records, null, 2));
}

export function addTx(record: TxRecord): void {
  const history = readHistory();
  // Avoid duplicates
  if (!history.some((r) => r.transactionId === record.transactionId)) {
    history.unshift(record);
    writeHistory(history);
  }
}

export function getTxHistory(): TxRecord[] {
  return readHistory();
}
