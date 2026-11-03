import { useState, type FormEvent } from 'react';
import type { AuthMe, BusquedaSedes, Presbiterio } from '../api/types';

interface Props {
  auth: AuthMe;
  presbiterios: Presbiterio[];
  sedes: BusquedaSedes;
  query: string;
  loadingSedes: boolean;
  submitting: boolean;
  error: string | null;
  notice: string | null;
  onSearch: (query: string) => void;
  onSelectSede: (sedeId: number) => Promise<void>;
  onCreateSede: (nombre: string, referencia: string | null) => Promise<void>;
  onChangePresbiterio: (presbiterioId: number) => Promise<void>;
  onLogout: () => Promise<void>;
}

export default function ContextScreen({
  auth,
  presbiterios,
  sedes,
  query,
  loadingSedes,
  submitting,
  error,
  notice,
  onSearch,
  onSelectSede,
  onCreateSede,
  onChangePresbiterio,
  onLogout,
}: Props) {
  const [showNewSede, setShowNewSede] = useState(false);
  const [nombre, setNombre] = useState('');
  const [referencia, setReferencia] = useState('');
  const [presbiterioDestino, setPresbiterioDestino] = useState(String(auth.presbiterio_id));

  const presbiterioNombre =
    auth.presbiterio_nombre ??
    presbiterios.find((presbiterio) => presbiterio.id === auth.presbiterio_id)?.nombre ??
    `Presbiterio ${auth.presbiterio_id}`;

  async function handleCreate(event: FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();
    if (nombre.trim().length < 2) {
      return;
    }
    await onCreateSede(nombre.trim(), referencia.trim() === '' ? null : referencia.trim());
  }

  return (
    <main className="work-shell">
      <header className="work-header">
        <div>
          <p className="eyebrow">PCD · Contexto de digitación</p>
          <h1>Selecciona la sede</h1>
          <p className="muted">
            Presbiterio autenticado: <strong>{presbiterioNombre}</strong>. Solo se muestran sus sedes.
          </p>
        </div>
        <button className="btn ghost" type="button" onClick={() => void onLogout()}>
          Cerrar sesión
        </button>
      </header>

      {error !== null && (
        <p className="notice error" role="alert">
          {error}
        </p>
      )}

      {notice !== null && (
        <p className="notice warn" role="status">
          {notice}
        </p>
      )}

      {auth.perfil === 'ADMIN' && (
        <section className="card" aria-labelledby="admin-context-title">
          <h2 id="admin-context-title">Cambio explícito de presbiterio (ADMIN)</h2>
          <p className="muted">
            Al cambiar de presbiterio se invalida la sede anterior. Es la única forma de operar
            en otro de los cuatro presbiterios precargados.
          </p>
          <div className="row">
            <label className="field grow" htmlFor="admin-presbiterio">
              <span>Presbiterio de destino</span>
              <select
                id="admin-presbiterio"
                value={presbiterioDestino}
                onChange={(event) => setPresbiterioDestino(event.target.value)}
              >
                {presbiterios.map((presbiterio) => (
                  <option key={presbiterio.id} value={presbiterio.id}>
                    {presbiterio.nombre} ({presbiterio.codigo})
                  </option>
                ))}
              </select>
            </label>
            <button
              className="btn"
              type="button"
              disabled={submitting || Number(presbiterioDestino) === auth.presbiterio_id}
              onClick={() => void onChangePresbiterio(Number(presbiterioDestino))}
            >
              Cambiar presbiterio
            </button>
          </div>
        </section>
      )}

      <section className="card" aria-labelledby="sede-title">
        <div className="row between wrap">
          <div>
            <h2 id="sede-title">Sede de digitación</h2>
            <p className="muted">Selecciona una sede existente o crea una nueva sin aprobación.</p>
          </div>
          <button
            className="btn soft"
            type="button"
            onClick={() => setShowNewSede((value) => !value)}
          >
            {showNewSede ? 'Cancelar nueva sede' : 'Nueva sede'}
          </button>
        </div>

        <label className="field" htmlFor="sede-search">
          <span>Buscar sede</span>
          <input
            id="sede-search"
            type="search"
            value={query}
            onChange={(event) => onSearch(event.target.value)}
            placeholder="Escribe para filtrar sedes…"
            autoComplete="off"
          />
        </label>

        {loadingSedes && <p className="notice info">Consultando sedes…</p>}

        {!loadingSedes && sedes.items.length === 0 && (
          <p className="notice info">
            No hay sedes coincidentes. Puedes crear una nueva si pertenece a este presbiterio.
          </p>
        )}

        {sedes.items.length > 0 && (
          <ul className="sede-list" aria-label="Sedes disponibles">
            {sedes.items.map((sede) => (
              <li key={sede.id}>
                <button
                  className="sede-option"
                  type="button"
                  disabled={submitting}
                  onClick={() => void onSelectSede(sede.id)}
                >
                  <span>{sede.nombre_oficial}</span>
                  {sede.ubicacion_referencia !== null && (
                    <small>{sede.ubicacion_referencia}</small>
                  )}
                </button>
              </li>
            ))}
          </ul>
        )}

        {sedes.similares.length > 0 && (
          <p className="notice warn" role="status">
            Sedes parecidas: {sedes.similares.map((sede) => sede.nombre_oficial).join('; ')}.
            Verifica que no sea la misma antes de crear otra.
          </p>
        )}

        {showNewSede && (
          <form className="stack new-sede" onSubmit={(event) => void handleCreate(event)} noValidate>
            <h3>Agregar nueva sede</h3>
            <p className="muted">Quedará vinculada al presbiterio autenticado; no hay aprobación de supervisor.</p>
            <label className="field" htmlFor="new-sede-name">
              <span>
                Nombre oficial de la nueva sede <span aria-hidden="true">*</span>
              </span>
              <input
                id="new-sede-name"
                type="text"
                value={nombre}
                onChange={(event) => setNombre(event.target.value)}
                placeholder="Nombre que consta en el papel"
                minLength={2}
                maxLength={200}
                disabled={submitting}
                required
              />
            </label>
            <label className="field" htmlFor="new-sede-reference">
              <span>Distrito / referencia (opcional)</span>
              <input
                id="new-sede-reference"
                type="text"
                value={referencia}
                onChange={(event) => setReferencia(event.target.value)}
                maxLength={300}
                disabled={submitting}
              />
            </label>
            <button className="btn primary" type="submit" disabled={submitting || nombre.trim().length < 2}>
              {submitting ? 'Guardando…' : 'Crear y seleccionar'}
            </button>
          </form>
        )}
      </section>

      <p className="footnote">
        Las tildes, mayúsculas y espacios se normalizan solo para detectar duplicados. El nombre
        visible se conserva tal como se escribe.
      </p>
    </main>
  );
}
