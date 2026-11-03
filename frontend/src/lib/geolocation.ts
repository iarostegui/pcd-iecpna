import type { LocationPayload } from '../api/types';

/**
 * Una única solicitud de ubicación por sesión. Devuelve el estado que se
 * persistirá en sesion; nunca se guardan coordenadas en el navegador.
 */
export function requestLocationOnce(): Promise<LocationPayload> {
  const capturadaEn = new Date().toISOString();
  const denied: LocationPayload = { estado: 'DENEGADA', latitud: null, longitud: null, precision_m: null, capturada_en: capturadaEn };
  const unavailable: LocationPayload = { estado: 'NO_DISPONIBLE', latitud: null, longitud: null, precision_m: null, capturada_en: capturadaEn };
  const failure: LocationPayload = { estado: 'ERROR', latitud: null, longitud: null, precision_m: null, capturada_en: capturadaEn };

  if (typeof navigator === 'undefined' || !('geolocation' in navigator)) {
    return Promise.resolve(unavailable);
  }

  return new Promise((resolve) => {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        resolve({
          estado: 'OK',
          latitud: position.coords.latitude,
          longitud: position.coords.longitude,
          precision_m: Number.isFinite(position.coords.accuracy) ? position.coords.accuracy : null,
          capturada_en: new Date().toISOString(),
        });
      },
      (error) => {
        if (error.code === 1) {
          resolve(denied);
          return;
        }
        if (error.code === 2) {
          resolve(unavailable);
          return;
        }
        resolve(failure);
      },
      { enableHighAccuracy: false, timeout: 10_000, maximumAge: 0 },
    );
  });
}
