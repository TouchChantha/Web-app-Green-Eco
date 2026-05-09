/**
 * Opens turn-by-turn navigation in Google Maps (browser or native app).
 * Works without a Maps JS API key — satisfies "Navigate route" for drivers.
 */
export function openGoogleMapsDirections(destination: string): void {
  const q = encodeURIComponent(destination.trim())
  if (!q) return
  const url = `https://www.google.com/maps/dir/?api=1&destination=${q}`
  window.open(url, '_blank', 'noopener,noreferrer')
}

export function phoneDialHref(raw: string | null | undefined): string | null {
  if (!raw?.trim()) return null
  const digits = raw.replace(/[^\d+]/g, '')
  return digits ? `tel:${digits}` : null
}
