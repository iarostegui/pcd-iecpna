import { useCallback, useEffect, useRef, useState } from 'react';
import { ApiError, api } from '../api/client';
import type { AuthMe } from '../api/types';

type Field =
  | 'tipo_documento' | 'numero_documento' | 'nombres' | 'apellido_paterno'
  | 'apellido_materno' | 'fecha_nacimiento' | 'sexo_codigo' | 'estado_civil_codigo'
  | 'telefono' | 'correo' | 'facebook_usuario' | 'direccion'
  | 'condicion_codigo' | 'fecha_condicion' | 'numero_ficha' | 'fecha_papel'
  | 'responsable_papel' | 'observaciones'
  | 'ubigeo_dpto_codigo' | 'ubigeo_prov_codigo' | 'ubigeo_dist_codigo'
  | 'conyuge_tipo_documento' | 'conyuge_numero_documento' | 'conyuge_nombres_apellidos'
  | 'nombre_sede_pastoreada' | 'region_presbitero' | 'descripcion'
  | 'fecha_desde' | 'otra_descripcion' | 'otra_fecha_desde';

type RecordForm = Record<Field, string>;

const EMPTY: RecordForm = {
  tipo_documento: '', numero_documento: '', nombres: '', apellido_paterno: '',
  apellido_materno: '', fecha_nacimiento: '', sexo_codigo: '', estado_civil_codigo: '',
  telefono: '', correo: '', facebook_usuario: '', direccion: '',
  condicion_codigo: '', fecha_condicion: '', numero_ficha: '', fecha_papel: '',
  responsable_papel: '', observaciones: '',
  ubigeo_dpto_codigo: '', ubigeo_prov_codigo: '', ubigeo_dist_codigo: '',
  conyuge_tipo_documento: '', conyuge_numero_documento: '', conyuge_nombres_apellidos: '',
  nombre_sede_pastoreada: '', region_presbitero: '', descripcion: '',
  fecha_desde: '', otra_descripcion: '', otra_fecha_desde: '',
};
type Ubigeo = { codigo: string; nombre: string; padre_codigo: string | null; tipo: string };
type Catalogo = { categoria: string; codigo: string; etiqueta: string; orden: number };
type SaveResult = { ficha_id: number; persona_id: number; persona_reutilizada: boolean; advertencias?: string[] };
type PersonaPage = { pagina: number; tamano: number; total: number; items: Array<{
  id: number; ficha_id: number; nombres: string | null; apellido_paterno: string | null;
  apellido_materno: string | null; presbiterio_id: number;
}> };

const groups: { title: string; fields: { key: Field; label: string; type?: string }[] }[] = [
  { title: 'Identificación', fields: [
    { key: 'tipo_documento', label: 'Tipo de documento' },
    { key: 'numero_documento', label: 'Número de documento (conservar ceros)' },
    { key: 'nombres', label: 'Nombres' },
    { key: 'apellido_paterno', label: 'Apellido paterno' },
    { key: 'apellido_materno', label: 'Apellido materno' },
    { key: 'fecha_nacimiento', label: 'Fecha de nacimiento', type: 'date' },
    { key: 'sexo_codigo', label: 'Sexo (código de ficha)' },
    { key: 'estado_civil_codigo', label: 'Estado civil (código de ficha)' },
  ] },
  { title: 'Contacto y condición', fields: [
    { key: 'telefono', label: 'Teléfono' }, { key: 'correo', label: 'Correo electrónico', type: 'email' },
    { key: 'facebook_usuario', label: 'Usuario Facebook' }, { key: 'direccion', label: 'Dirección' },
    { key: 'condicion_codigo', label: 'Condición (código)' },
    { key: 'fecha_condicion', label: 'Fecha de condición', type: 'date' },
  ] },
  { title: 'Datos del formulario físico', fields: [
    { key: 'numero_ficha', label: 'Número de ficha (opcional)' },
    { key: 'fecha_papel', label: 'Fecha de papel', type: 'date' },
    { key: 'responsable_papel', label: 'Responsable en papel' },
    { key: 'observaciones', label: 'Observaciones' },
  ] },
  { title: 'Cónyuge — sección opcional', fields: [
    { key: 'conyuge_tipo_documento', label: 'Tipo documento del cónyuge' },
    { key: 'conyuge_numero_documento', label: 'Número documento del cónyuge' },
    { key: 'conyuge_nombres_apellidos', label: 'Nombres y apellidos' },
  ] },
  { title: 'Responsabilidades — sección opcional', fields: [
    { key: 'nombre_sede_pastoreada', label: 'Sede pastoreada' },
    { key: 'region_presbitero', label: 'Región del presbítero' },
    { key: 'descripcion', label: 'Descripción de responsabilidad' },
    { key: 'fecha_desde', label: 'Desde', type: 'date' },
    { key: 'otra_descripcion', label: 'Otra responsabilidad' },
    { key: 'otra_fecha_desde', label: 'Desde (otra)', type: 'date' },
  ] },
];

function key(): string {
  return typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() :
    `persona-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

export default function PersonaCapture({ auth }: { auth: AuthMe }) {
  const [form, setForm] = useState<RecordForm>({ ...EMPTY });
  const [bools, setBools] = useState({ es_lider_gc_intencion: false, es_pastor_sede: false, es_presbitero: false, tiene_responsabilidad: false });
  const [busy, setBusy] = useState(false);
  const [control, setControl] = useState({ ficha_completa: false, falta_informacion: false, persona_validada: false });
  const [error, setError] = useState<string | null>(null);
  const [saved, setSaved] = useState<SaveResult | null>(null);
  const [personaAnexar, setPersonaAnexar] = useState('');
  const [ubigeos, setUbigeos] = useState<Ubigeo[]>([]);
  const [catalogos, setCatalogos] = useState<Catalogo[]>([]);
  const [provincias, setProvincias] = useState<Ubigeo[]>([]);
  const [distritos, setDistritos] = useState<Ubigeo[]>([]);
  const [recent, setRecent] = useState<PersonaPage | null>(null);
  const idem = useRef<string | null>(null);

  const loadRecent = useCallback(async () => {
    try { setRecent(await api.get<PersonaPage>('/personas?pagina=1&tamano=10')); }
    catch { /* solo complementario, la captura sigue disponible */ }
  }, []);

  useEffect(() => {
    void api.get<Ubigeo[]>('/ubigeos?tipo=DEPARTAMENTO').then(setUbigeos).catch(() => setUbigeos([]));
    void api.get<Catalogo[]>('/catalogos').then(setCatalogos).catch(() => setCatalogos([]));
    void loadRecent();
  }, [loadRecent]);

  useEffect(() => {
    if (!form.ubigeo_dpto_codigo) { setProvincias([]); return; }
    void api.get<Ubigeo[]>(`/ubigeos?tipo=PROVINCIA&padre_codigo=${form.ubigeo_dpto_codigo}`)
      .then(setProvincias).catch(() => setProvincias([]));
  }, [form.ubigeo_dpto_codigo]);

  useEffect(() => {
    if (!form.ubigeo_prov_codigo) { setDistritos([]); return; }
    void api.get<Ubigeo[]>(`/ubigeos?tipo=DISTRITO&padre_codigo=${form.ubigeo_prov_codigo}`)
      .then(setDistritos).catch(() => setDistritos([]));
  }, [form.ubigeo_prov_codigo]);

  function change(field: Field, value: string): void {
    setForm(current => ({
      ...current, [field]: value,
      ...(field === 'ubigeo_dpto_codigo' ? { ubigeo_prov_codigo: '', ubigeo_dist_codigo: '' } : {}),
      ...(field === 'ubigeo_prov_codigo' ? { ubigeo_dist_codigo: '' } : {}),
    }));
    idem.current = null;
    setSaved(null);
  }

  function buildPayload(): Record<string, unknown> {
    const payload: Record<string, unknown> = {};
    for (const [name, value] of Object.entries(form)) {
      if (value === '' || name.startsWith('conyuge_') || [
        'nombre_sede_pastoreada', 'region_presbitero', 'descripcion', 'fecha_desde',
        'otra_descripcion', 'otra_fecha_desde',
      ].includes(name)) continue;
      payload[name] = value;
    }
    payload.es_lider_gc_intencion = bools.es_lider_gc_intencion;
    if (Object.values(control).some(Boolean)) payload.control_papel = JSON.stringify(control);
    const conyuge = {
      tipo_documento: form.conyuge_tipo_documento || undefined,
      numero_documento: form.conyuge_numero_documento || undefined,
      nombres_apellidos: form.conyuge_nombres_apellidos || undefined,
    };
    if (Object.values(conyuge).some(Boolean)) payload.conyuge = conyuge;
    const responsabilidades = {
      es_pastor_sede: bools.es_pastor_sede, nombre_sede_pastoreada: form.nombre_sede_pastoreada || undefined,
      es_presbitero: bools.es_presbitero, region_presbitero: form.region_presbitero || undefined,
      tiene_responsabilidad: bools.tiene_responsabilidad, descripcion: form.descripcion || undefined,
      fecha_desde: form.fecha_desde || undefined, otra_descripcion: form.otra_descripcion || undefined,
      otra_fecha_desde: form.otra_fecha_desde || undefined,
    };
    if (Object.values(responsabilidades).some(v => v === true || (typeof v === 'string' && v !== ''))) {
      payload.responsabilidades = responsabilidades;
    }
    return payload;
  }

  async function save(event: React.FormEvent): Promise<void> {
    event.preventDefault();
    if (busy || saved || auth.perfil === 'REPORTES') return;
    setBusy(true);
    setError(null);
    try {
      const id = personaAnexar.trim();
      if (id && (!/^\d+$/.test(id) || Number(id) <= 0)) {
        setError('El ID de Persona existente debe ser numérico.'); return;
      }
      idem.current ??= key();
      const path = id ? `/personas/${id}/fichas` : '/personas';
      const response = await api.post<SaveResult>(path, buildPayload(), { 'Idempotency-Key': idem.current });
      setSaved(response);
      idem.current = null;
      await loadRecent();
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'No fue posible guardar la ficha.');
    } finally {
      setBusy(false);
    }
  }

  function reset(): void {
    setForm({ ...EMPTY }); setBools({ es_lider_gc_intencion: false, es_pastor_sede: false, es_presbitero: false, tiene_responsabilidad: false });
    setControl({ ficha_completa: false, falta_informacion: false, persona_validada: false });
    setPersonaAnexar(''); setError(null); setSaved(null); idem.current = null;
  }

  return (
    <section className="card" aria-labelledby="i02-capture-title">
      <h2 id="i02-capture-title">I02 · Captura de Persona y ficha</h2>
      <p className="muted">Datos exclusivamente sintéticos. Se conserva una ficha por fuente y la identidad documental confiable se reutiliza sin sobrescribirla.</p>
      <p className="notice info">H-24: presbiterio, sede, nombres, ambos apellidos, tipo y número de documento y teléfono obligatorios. Los ubigeos se toman del archivo geográfico suministrado para digitación, sin acreditar corte INEI. C.E. se captura sin fusión automática. Solo datos sintéticos durante UAT.</p>
      <p className="muted">Contexto de la ficha: <strong>{auth.presbiterio_nombre ?? `Presbiterio #${auth.presbiterio_id}`}</strong> · <strong>{auth.sede_nombre ?? 'Sede no seleccionada'}</strong>. Estos datos se registran desde la sesión.</p>

      <form onSubmit={e => void save(e)} className="capture-form">
        <label>
          Anexar a Persona existente (ID, opcional; requiere DNI concordante)
          <input inputMode="numeric" value={personaAnexar} onChange={e => { setPersonaAnexar(e.target.value); idem.current = null; }} />
        </label>
        {groups.map(group => (
          <fieldset key={group.title} className="capture-group">
            <legend>{group.title}</legend>
            <div className="capture-grid">
              {group.fields.map(field => (
                <label key={field.key}>
                  {field.label}
                  {field.key === 'tipo_documento' || field.key === 'conyuge_tipo_documento' ? (
                    <select required={field.key === 'tipo_documento'} value={form[field.key]} onChange={e => change(field.key, e.target.value)}>
                      <option value="">Sin documento</option>
                      <option value="DNI">DNI</option><option value="CE">C.E.</option><option value="OTRO">OTRO</option>
                    </select>
                  ) : (['sexo_codigo', 'estado_civil_codigo', 'condicion_codigo'] as Field[]).includes(field.key) ? (
                    <select value={form[field.key]} onChange={e => change(field.key, e.target.value)}>
                      <option value="">Sin especificar</option>
                      {catalogos.filter(item => item.categoria === ({
                        sexo_codigo: 'SEXO', estado_civil_codigo: 'ESTADO_CIVIL',
                        condicion_codigo: 'CONDICION',
                      } as Record<string, string>)[field.key]).map(item => (
                        <option key={item.codigo} value={item.codigo}>{item.etiqueta}</option>
                      ))}
                    </select>
                  ) : (
                    <input required={(['numero_documento', 'nombres', 'apellido_paterno', 'apellido_materno', 'telefono'] as Field[]).includes(field.key)} type={field.type ?? 'text'} value={form[field.key]}
                      onChange={e => change(field.key, e.target.value)} />
                  )}
                </label>
              ))}
            </div>
          </fieldset>
        ))}

        <fieldset className="capture-group">
          <legend>Ubicación geográfica — referencia entregada para digitación</legend>
          {ubigeos.length === 0 && <p className="muted">Catálogo geográfico aún no cargado. Deja la ubicación sin seleccionar.</p>}
          <div className="capture-grid">
            {([
              ['ubigeo_dpto_codigo', 'Departamento', ubigeos],
              ['ubigeo_prov_codigo', 'Provincia', provincias],
              ['ubigeo_dist_codigo', 'Distrito', distritos],
            ] as const).map(([field, label, values]) => (
              <label key={field}>{label}
                <select value={form[field]} onChange={e => change(field, e.target.value)}>
                  <option value="">Sin especificar</option>
                  {values.map(value => <option key={value.codigo} value={value.codigo}>{value.nombre}</option>)}
                </select>
              </label>
            ))}
          </div>
        </fieldset>

        <fieldset className="capture-group">
          <legend>Indicadores del papel</legend>
          <div className="capture-grid">
            {([
              ['es_lider_gc_intencion', 'Intención de liderar Grupo Crecer'],
              ['es_pastor_sede', 'Es pastor de sede'],
              ['es_presbitero', 'Es presbítero'],
              ['tiene_responsabilidad', 'Tiene responsabilidad'],
            ] as const).map(([field, label]) => (
              <label key={field} className="capture-check">
                <input type="checkbox" checked={bools[field]}
                  onChange={e => { setBools(old => ({ ...old, [field]: e.target.checked })); idem.current = null; }} />
                {label}
              </label>
            ))}
          </div>
        </fieldset>

        <fieldset className="capture-group">
          <legend>Control de digitalización (marcas del formulario físico)</legend>
          <p className="muted">Transcribir únicamente lo que esté marcado en el papel. No constituye una validación institucional del PCD.</p>
          <div className="capture-grid">
            {([
              ['ficha_completa', 'Ficha completa'],
              ['falta_informacion', 'Falta información'],
              ['persona_validada', 'Persona validada en papel'],
            ] as const).map(([field, label]) => (
              <label key={field} className="capture-check">
                <input type="checkbox" checked={control[field]} onChange={e => {
                  setControl(old => ({
                    ...old, [field]: e.target.checked,
                    ...(e.target.checked && field === 'ficha_completa' ? { falta_informacion: false } : {}),
                    ...(e.target.checked && field === 'falta_informacion' ? { ficha_completa: false } : {}),
                  }));
                  idem.current = null;
                }} />
                {label}
              </label>
            ))}
          </div>
        </fieldset>

        {error && <p role="alert" className="notice warn">{error}</p>}
        {saved && <p className="muted">Para evitar registros duplicados, pulsa «Nueva captura» antes de registrar otra ficha.</p>}\n        {saved && <p role="status" className="notice info">
          Ficha #{saved.ficha_id} registrada. Persona #{saved.persona_id}.
          {saved.persona_reutilizada ? ' Se reutilizó la identidad existente.' : ' Nueva Persona.'}
          {saved.advertencias?.length ? ` ${saved.advertencias.join(' ')}` : ''}
        </p>}
        <div className="row">
          <button className="btn primary" type="submit" disabled={busy || Boolean(saved) || auth.sede_id === null || auth.perfil === 'REPORTES'}>{busy ? 'Guardando…' : saved ? 'Ficha registrada' : 'Registrar ficha'}</button>
          <button className="btn soft" type="button" onClick={reset} disabled={busy}>Nueva captura</button>
        </div>
      </form>
      {recent && <aside className="capture-recent">
        <h3>Últimas fichas registradas</h3>
        <p className="muted">Consulta autorizada sin restricción territorial; la edición se implementa en I04.</p>
        {recent.items.length === 0 ? <p>Aún no hay fichas.</p> : <ul>
          {recent.items.map(row => <li key={row.ficha_id}>
            Ficha #{row.ficha_id} · Persona #{row.id} · {[row.nombres, row.apellido_paterno, row.apellido_materno].filter(Boolean).join(' ') || 'Sin nombre'}
          </li>)}
        </ul>}
      </aside>}
    </section>
  );
}
