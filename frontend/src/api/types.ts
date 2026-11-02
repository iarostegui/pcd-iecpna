export interface ErrorEnvelope {
  error: {
    code: string;
    message: string;
    details?: Record<string, unknown>;
  };
  request_id: string;
}

export interface HealthResponse {
  ok: boolean;
  request_id: string;
  status: string;
  service: string;
  version: string;
  env: string;
  database: {
    status: 'up' | 'down';
  };
}
