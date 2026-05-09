
/**
 * google-maps.d.ts
 * Minimal type declarations for the Google Maps JavaScript API.
 * These allow TypeScript to compile without errors when using
 * google.maps.Map, Marker, Polyline, InfoWindow, etc.
 *
 * In production you can install the full types with:
 *   npm install --save-dev @types/google.maps
 */

declare namespace google {
  namespace maps {
    // ── Core ──────────────────────────────────────────────────────────────
    class LatLng {
      constructor(lat: number, lng: number)
      lat(): number
      lng(): number
    }
    class LatLngBounds {
      constructor()
      extend(point: LatLng): void
    }

    // ── Map ───────────────────────────────────────────────────────────────
    class Map {
      constructor(el: HTMLElement, options: MapOptions)
      setCenter(center: { lat: number; lng: number }): void
      fitBounds(bounds: LatLngBounds, padding?: number | { top: number; right: number; bottom: number; left: number }): void
    }
    interface MapOptions {
      center: { lat: number; lng: number }
      zoom: number
      styles?: any[]
      mapTypeControl?: boolean
      streetViewControl?: boolean
      fullscreenControl?: boolean
      zoomControl?: boolean
    }

    // ── Marker ────────────────────────────────────────────────────────────
    class Marker {
      constructor(options: MarkerOptions)
      setPosition(pos: LatLng): void
      getPosition(): LatLng | null | undefined
      setIcon(icon: Symbol | string): void
      setVisible(v: boolean): void
      setMap(map: Map | null): void
      addListener(event: string, handler: () => void): void
    }
    interface MarkerOptions {
      position: LatLng
      map?: Map
      icon?: Symbol | string
      label?: MarkerLabel | string
      title?: string
      zIndex?: number
    }
    interface MarkerLabel {
      text: string
      color?: string
      fontSize?: string
      fontWeight?: string
    }

    // ── Symbol (custom icon) ──────────────────────────────────────────────
    interface Symbol {
      path: SymbolPath | string
      scale?: number
      fillColor?: string
      fillOpacity?: number
      strokeColor?: string
      strokeWeight?: number
      strokeOpacity?: number
    }
    enum SymbolPath {
      CIRCLE = 0,
      FORWARD_CLOSED_ARROW = 1,
      FORWARD_OPEN_ARROW = 2,
      BACKWARD_CLOSED_ARROW = 3,
      BACKWARD_OPEN_ARROW = 4,
    }

    // ── Polyline ──────────────────────────────────────────────────────────
    class Polyline {
      constructor(options: PolylineOptions)
      setPath(path: LatLng[]): void
      setMap(map: Map | null): void
      setVisible(v: boolean): void
    }
    interface PolylineOptions {
      path?: LatLng[]
      map?: Map
      strokeColor?: string
      strokeOpacity?: number
      strokeWeight?: number
      geodesic?: boolean
      icons?: IconSequence[]
    }
    interface IconSequence {
      icon: Symbol
      offset?: string
      repeat?: string
    }

    // ── InfoWindow ────────────────────────────────────────────────────────
    class InfoWindow {
      constructor(options?: InfoWindowOptions)
      setContent(content: string): void
      open(map: Map, anchor?: Marker): void
      close(): void
    }
    interface InfoWindowOptions {
      content?: string
    }
  }
}
