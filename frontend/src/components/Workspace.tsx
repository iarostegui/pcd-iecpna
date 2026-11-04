import PersonaCapture from './PersonaCapture';
import type { AuthMe, GeoEstado } from '../api/types';

interface Props {
  auth: AuthMe;
  gpsState: GeoEstado | null;
  notice?: string | null;
  onOpenContext: () => void;
  onLogout: () => Promise<void>;
}

const PERFIL_LABEL: Record<AuthMe['perfil'], string> = {
  DIGITADOR: 'Digitador',
  ADMIN: 'Administrador nacional',
  REPORTES: 'Reportes nacional',
};

const GEO_LABEL: Record<GeoEstado, string> = {
  OK: 'Ubicación registrada',
  DENEGADA: 'Ubicación denegada',
  NO_DISPONIBLE: 'Ubicación no disponible',
  ERROR: 'Ubicación con error',
};

export default function Workspace({ auth, gpsState, notice, onOpenContext, onLogout }: Props) {
  const usuario = auth.usuario_nombre ?? auth.usuario_codigo ?? `Usuario ${auth.usuario_id}`;

  return (
    <main className="work-shell">
      <header className="work-header">
        <div>
          <p className="eyebrow">PCD · Sesión autenticada</p>
          <h1>{usuario}</h1>
          <p className="muted">
            {PERFIL_LABEL[auth.perfil]}
            {auth.usuario_codigo !== undefined && ` · ${auth.usuario_codigo}`}
          </p>
        </div>
        <div className="row">
          {auth.perfil !== 'REPORTES' && (
            <button className="btn soft" type="button" onClick={onOpenContext}>
              Cambiar sede
            </button>
          )}
          <button className="btn ghost" type="button" onClick={() => void onLogout()}>
            Cerrar sesión
          </button>
        </div>
      </header>

      {notice !== undefined && notice !== null && (
        <p className="notice warn" role="status">
          {notice}
        </p>
      )}

      <section className="card" aria-labelledby="contexto-actual">
        <h2 id="contexto-actual">Contexto operativo</h2>
        <dl className="facts">
          <div>
            <dt>Presbiterio</dt>
            <dd>{auth.presbiterio_nombre ?? `ID ${auth.presbiterio_id}`}</dd>
          </div>
          <div>
            <dt>Sede</dt>
            <dd>{auth.sede_nombre ?? (auth.perfil === 'REPORTES' ? 'Sin sede (consulta nacional)' : 'Sin seleccionar')}</dd>
          </div>
          <div>
            <dt>Perfil</dt>
            <dd>{auth.perfil}</dd>
          </div>
          <div>
            <dt>Ubicación de sesión</dt>
            <dd>{gpsState === null ? 'No solicitada' : GEO_LABEL[gpsState]}</dd>
          </div>
        </dl>
        <p className="muted small">
          La ubicación es opcional, se solicita como máximo una vez por sesión y no se usa como
          prueba de autoría ni restricción de acceso.
        </p>
      </section>

      <section className="card" aria-labelledby="alcance-i01">
        <h2 id="alcance-i01">Sesión y contexto habilitados</h2>
        {auth.perfil === 'REPORTES' ? (
          <p>
            El perfil REPORTES accede en modo de solo lectura. Los informes nacionales pertenecen a
            I05 y no se habilitan en I01.
          </p>
        ) : (
          <p>
            El contexto de presbiterio y sede está listo. La captura de Persona y ficha está habilitada en I02. El registro de Grupo Crecer corresponde a I03.
          </p>
        )}
        <p className="notice info">
          La sesión expira por inactividad o
          por duración absoluta; cualquier respuesta 401 devuelve al login.
        </p>
      </section>
      {auth.perfil !== 'REPORTES' && auth.sede_id !== null && <PersonaCapture auth={auth} />}
    </main>
  );
}
