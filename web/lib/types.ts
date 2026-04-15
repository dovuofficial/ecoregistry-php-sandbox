export interface Serial {
  serial: string;
  quantity: number;
  quantity_lock: number;
}

export interface CompanyBalance {
  company_id: string;
  serials: Serial[];
}

export interface PositionsResponse {
  status: boolean;
  balance: CompanyBalance[];
}

export interface ProjectLocation {
  city: string;
  country: string;
  region: string;
}

export interface ExchangeProject {
  id: number;
  name: string;
  description: string;
  is_active: boolean;
  standard: string;
  stage: string;
  credits_type: string;
  sector: number[];
  evaluation_criteria: string;
  quantification_method: string;
  validator: string;
  verifier: string;
  owner: string;
  owner_id: string;
  developer: string;
  developer_id: string;
  locations: ProjectLocation[];
}

export interface EligibleReason {
  reasonUsingCarbonOffsetsId: number;
  code: string;
  description: string;
  passive_subject_required: number;
}

export interface RetirementInput {
  serial: string;
  quantity: number;
  reasonId: number;
  observation: string;
  endUser: {
    name: string;
    countryId: number;
    documentTypeId: number;
    documentNumber: string;
  };
}

export interface RetirementResult {
  success: boolean;
  result?: {
    data: { serial: string; quantity: number; date: string };
    urlPDF: string;
    transactionId: number;
  };
  error?: string;
  detail?: unknown;
}

export interface DebugInfo {
  script: string;
  args: string[];
  stdout: string;
  stderr: string;
  exitCode: number | null;
  durationMs: number;
}

export type Account = 'general' | 'user';
