/**
 * useGoogleMaps.ts
 * Loads Google Maps JS API using the recommended bootstrap loader pattern.
 * Uses (g,s,t) inline bootstrap + loading=async to avoid the
 * "loaded directly without loading=async" warning.
 */

let _promise: Promise<void> | null = null

function getKey(): string {
  const envKey = (import.meta.env.VITE_GOOGLE_MAPS_KEY as string) || ''
  if (envKey && envKey !== 'YOUR_GOOGLE_MAPS_API_KEY_HERE') return envKey.trim()
  const meta = document.querySelector('meta[name="gmkey"]')
  return (meta?.getAttribute('content') || '').trim()
}

export function loadGoogleMaps(): Promise<void> {
  if (typeof window !== 'undefined' && (window as any).google?.maps?.Map) {
    return Promise.resolve()
  }
  if (_promise) return _promise

  _promise = new Promise<void>((resolve, reject) => {
    const key = getKey()
    if (!key) {
      console.warn('[GoogleMaps] No API key found.')
      resolve()
      return
    }

    console.log('[GoogleMaps] Loading with key:', key.slice(0, 10) + '...')

    // Use the recommended async bootstrap loader pattern.
    // This avoids the "loaded directly without loading=async" warning.
    const g = window as any
    g.__googleMapsCallback = () => {
      delete g.__googleMapsCallback
      console.log('[GoogleMaps] Ready ✓')
      resolve()
    }

    const script = document.createElement('script')
    // loading=async is the key parameter that suppresses the warning
    script.src = `https://maps.googleapis.com/maps/api/js?key=${key}&callback=__googleMapsCallback&libraries=marker&loading=async&v=weekly`
    script.async = true
    script.defer = true
    script.onerror = () => {
      _promise = null
      console.error('[GoogleMaps] Failed to load — check key & billing')
      reject(new Error('Google Maps script failed'))
    }
    document.head.appendChild(script)
  })

  return _promise
}

export function isMapsLoaded(): boolean {
  return typeof (window as any).google?.maps?.Map === 'function'
}
