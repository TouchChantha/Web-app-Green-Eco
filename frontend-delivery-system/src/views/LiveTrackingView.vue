<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-800">Real-time map dashboard</h2>
        <p class="text-sm text-slate-500 mt-0.5">Live driver positions and active delivery routes</p>
      </div>
      <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full font-semibold">Admin only</span>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm" style="overflow:visible;">
      <div class="flex flex-col lg:flex-row" style="overflow:visible;">

        <div class="flex-1 flex flex-col" style="overflow:visible;">
          <!-- The map div — NO overflow:hidden anywhere above this element -->
          <div ref="mapEl"
            style="height:460px; width:100%; border-radius:12px 0 0 12px; overflow:hidden;"
          ></div>

          <div class="px-5 py-3 border-t border-slate-100 flex flex-wrap items-center gap-3 bg-white rounded-bl-2xl">
            <select v-model="filterStatus" @change="applyFilters"
              class="border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-600 bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-green-600">
              <option value="">Filter by status</option>
              <option value="on_delivery">On delivery</option>
              <option value="available">Available</option>
              <option value="offline">Offline</option>
            </select>
            <select v-model="filterDriver" @change="applyFilters"
              class="border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-600 bg-white cursor-pointer min-w-36 focus:outline-none focus:ring-2 focus:ring-green-600">
              <option value="">Filter driver</option>
              <option v-for="d in allDots" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <button @click="manualRefresh" :disabled="refreshing"
              class="flex items-center gap-2 px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-colors disabled:opacity-50 bg-white">
              <svg :class="[`w-4 h-4`, refreshing ? `animate-spin` : ``]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
              Refresh
            </button>
            <div class="flex items-center gap-1.5 ml-auto text-xs text-slate-400">
              <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
              {{ lastRefresh }}
            </div>
          </div>
        </div>

        <div class="lg:w-60 xl:w-64 border-t lg:border-t-0 lg:border-l border-slate-200 flex flex-col bg-white rounded-tr-2xl rounded-br-2xl">
          <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Active drivers</h3>
          </div>

          <div class="flex-1 divide-y divide-slate-100 overflow-y-auto">
            <div v-for="d in allDots" :key="d.id"
              class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors cursor-pointer"
              :class="selectedDriver && selectedDriver.id === d.id ? `bg-green-50 border-l-2 border-green-600` : ``"
              @click="selectDriver(d)">
              <span class="w-2.5 h-2.5 rounded-full shrink-0"
                :class="d.delayed ? `bg-red-500` : d.status === `on_delivery` ? `bg-amber-400` : d.status === `available` ? `bg-green-600` : `bg-slate-400`">
              </span>
              <span class="flex-1 text-sm font-medium text-slate-800 truncate">{{ d.name }}</span>
              <span class="text-xs px-2 py-0.5 rounded-full font-semibold shrink-0"
                :class="d.delayed ? `bg-red-100 text-red-700` : d.status === `on_delivery` ? `bg-blue-100 text-blue-700` : d.status === `available` ? `bg-green-100 text-green-700` : `bg-slate-100 text-slate-500`">
                {{ d.delayed ? `Delayed` : d.status === `on_delivery` ? `Transit` : d.status === `available` ? `Available` : `Offline` }}
              </span>
            </div>
            <div v-if="allDots.length === 0" class="py-10 text-center text-sm text-slate-400">No drivers online</div>
          </div>

          <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">
            <p class="text-xs font-semibold text-slate-600 mb-2">Click driver to see</p>
            <ul class="space-y-1.5 text-xs text-slate-500">
              <li>— Current order</li>
              <li>— Route polyline</li>
              <li>— Speed + heading</li>
              <li>— ETA to delivery</li>
            </ul>
          </div>

          <div v-if="selectedDriver" class="border-t border-slate-200 bg-white p-4">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-9 h-9 rounded-full bg-green-700 flex items-center justify-center text-white font-bold text-sm shrink-0">
                {{ selectedDriver.name.slice(0,2).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800">{{ selectedDriver.name }}</p>
                <p class="text-xs text-slate-400 truncate">{{ selectedDriver.vehicle }}</p>
              </div>
              <button @click="clearSelection" class="text-slate-400 hover:text-slate-600 shrink-0 text-lg leading-none">&times;</button>
            </div>
            <div class="space-y-2 text-xs bg-slate-50 rounded-xl p-3 border border-slate-200">
              <div class="flex justify-between"><span class="text-slate-500">Order</span><span class="font-mono font-bold text-green-700">{{ selectedDriver.order || `—` }}</span></div>
              <div class="flex justify-between"><span class="text-slate-500">ETA</span><span class="font-semibold text-blue-600">{{ etaData[selectedDriver.id] || selectedDriver.eta || `—` }}</span></div>
              <div class="flex justify-between"><span class="text-slate-500">Speed</span><span class="font-semibold text-slate-700">{{ selectedDriver.speed || `—` }}</span></div>
              <div class="flex justify-between"><span class="text-slate-500">Status</span>
                <span :class="selectedDriver.delayed ? `text-red-600 font-semibold` : `text-slate-700`">
                  {{ selectedDriver.delayed ? `Delayed ⚠` : selectedDriver.status.replace(`_`,` `) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes pulse-red {
  0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.5); }
  50%       { box-shadow: 0 0 0 10px rgba(239,68,68,0); }
}
</style>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue"
import { useDriversStore } from "../stores/drivers"
import { routesApi } from "../api/routes"
import { loadGoogleMaps, isMapsLoaded } from "../composables/useGoogleMaps"

const driversStore = useDriversStore()
const mapEl = ref<HTMLElement | null>(null)

const refreshing     = ref(false)
const lastRefresh    = ref("—")
const selectedDriver = ref<any>(null)
const filterStatus   = ref("")
const filterDriver   = ref("")
const etaData        = ref<Record<number, string>>({})

let gmap: any = null
let infoWindow: any = null
let routePolyline: any = null
let destinationMarker: any = null
const driverMarkers   = new Map<number, { marker: any; trail: any }>()
const locationHistory = new Map<number, any[]>()
let refreshTimer: ReturnType<typeof setInterval> | null = null

const PP = { lat: 11.5564, lng: 104.9282 }

const DEMO = [
  { id:1, name:"Dara Sok",   status:"on_delivery", delayed:false, vehicle:"Motorcycle · PP-1234A", order:"ORD-A1B2", orderId:null, eta:"22 min", speed:"35 km/h", lat:11.5520, lng:104.9200 },
  { id:2, name:"Kosal Meas", status:"on_delivery", delayed:false, vehicle:"Motorcycle · PP-5678B", order:"ORD-C3D4", orderId:null, eta:"18 min", speed:"28 km/h", lat:11.5480, lng:104.9350 },
  { id:3, name:"Thy Chan",   status:"available",   delayed:false, vehicle:"Car · PP-9012C",        order:null,       orderId:null, eta:null,     speed:null,       lat:11.5650, lng:104.9450 },
  { id:4, name:"Bopha Srun", status:"on_delivery", delayed:true,  vehicle:"Motorcycle · PP-3456D", order:"ORD-G7H8", orderId:null, eta:"35 min", speed:"22 km/h", lat:11.5430, lng:104.9280 },
]

const allDots = computed<any[]>(() => {
  const live: any[] = driversStore.liveLocations || []
  if (live.length > 0) {
    return live.map((loc: any, i: number) => ({
      // Backend returns: id, name, status, vehicle, lat, lng, last_updated
      id:      loc.id      ?? loc.driver_id ?? i + 1,
      name:    loc.name    ?? loc.driver_name ?? ('Driver ' + (i + 1)),
      status:  loc.status  ?? 'available',
      delayed: !!loc.is_delayed,
      vehicle: loc.vehicle ?? ((loc.vehicle_type ?? '') + ' · ' + (loc.vehicle_plate ?? '')).trim(),
      order:   loc.current_order_number ?? null,
      orderId: loc.current_order_id     ?? null,
      eta:     loc.eta   ?? null,
      speed:   loc.speed ? (loc.speed + ' km/h') : null,
      lat:     loc.lat   ?? loc.latitude,
      lng:     loc.lng   ?? loc.longitude,
    }))
  }
  return DEMO
})

const filteredDots = computed<any[]>(() => {
  let dots = allDots.value
  if (filterStatus.value) dots = dots.filter((d: any) => d.status === filterStatus.value)
  if (filterDriver.value) dots = dots.filter((d: any) => d.id === Number(filterDriver.value))
  return dots
})

function color(dot: any): string {
  return dot.delayed ? "#ef4444" : dot.status === "on_delivery" ? "#3b82f6" : "#16a34a"
}

function initMap() {
  if (!mapEl.value) return
  const g = (window as any).google
  if (!g?.maps?.Map) return
  gmap = new g.maps.Map(mapEl.value, {
    center: PP, zoom: 13,
    mapId: 'DEMO_MAP_ID',          // required for AdvancedMarkerElement
    mapTypeControl: false, streetViewControl: false,
    fullscreenControl: true, zoomControl: true,
  })
  infoWindow = new g.maps.InfoWindow()
  console.log('[Map] Google Maps initialized ✓')
}

/** Build a colored circle element for AdvancedMarkerElement */
function makeMarkerEl(dot: any): HTMLElement {
  const el = document.createElement('div')
  const c = color(dot)
  el.style.cssText = `
    width:28px; height:28px; border-radius:50%;
    background:${c}; border:3px solid #fff;
    box-shadow:0 2px 8px rgba(0,0,0,0.35);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:10px; font-weight:700; color:#fff;
    font-family:Inter,sans-serif;
  `
  el.textContent = dot.vehicle?.startsWith('M') ? 'M' : 'C'
  if (dot.delayed) {
    el.style.animation = 'pulse-red 1.5s infinite'
  }
  return el
}

function upsertMarker(dot: any) {
  if (!gmap) return
  const g = (window as any).google
  const pos = { lat: dot.lat ?? PP.lat, lng: dot.lng ?? PP.lng }

  if (driverMarkers.has(dot.id)) {
    const { marker, trail } = driverMarkers.get(dot.id)!
    // Update position
    marker.position = pos
    // Update icon color
    const el = marker.content as HTMLElement
    if (el) el.style.background = color(dot)
    // Update trail
    const latLng = new g.maps.LatLng(pos.lat, pos.lng)
    const h = locationHistory.get(dot.id) ?? []
    h.push(latLng); if (h.length > 10) h.shift()
    locationHistory.set(dot.id, h); trail.setPath(h)
  } else {
    // Use AdvancedMarkerElement (no deprecation warning)
    const AdvancedMarker = g.maps.marker?.AdvancedMarkerElement ?? g.maps.Marker
    const markerEl = makeMarkerEl(dot)

    const marker = new AdvancedMarker({
      position: pos,
      map: gmap,
      content: markerEl,
      title: dot.name,
      zIndex: dot.delayed ? 100 : 10,
    })

    // AdvancedMarkerElement fires 'gmp-click', not 'click'
    marker.addListener('gmp-click', () => openInfoWindow(dot, marker))

    const latLng = new g.maps.LatLng(pos.lat, pos.lng)
    const trail = new g.maps.Polyline({
      map: gmap, strokeColor: '#16a34a', strokeOpacity: 0, strokeWeight: 2,
      icons: [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: 0.5, scale: 3 }, offset: '0', repeat: '12px' }],
    })
    locationHistory.set(dot.id, [latLng])
    driverMarkers.set(dot.id, { marker, trail })
  }
}

async function openInfoWindow(dot: any, marker: any) {
  if (!infoWindow || !gmap) return
  selectedDriver.value = dot
  infoWindow.setContent(buildHTML(dot, dot.eta ?? '—'))
  // AdvancedMarkerElement uses open({map, anchor}) syntax
  if (marker.content) {
    infoWindow.open({ map: gmap, anchor: marker })
  } else {
    infoWindow.open(gmap, marker)
  }
  drawRoute(dot)
  if (dot.orderId) {
    try {
      const res = await routesApi.eta(dot.orderId)
      const eta = res.data.data?.eta_minutes ? (Math.round(res.data.data.eta_minutes) + ' min') : (dot.eta ?? '—')
      etaData.value[dot.id] = eta
      infoWindow.setContent(buildHTML(dot, eta))
    } catch { /* keep original */ }
  }
}

function buildHTML(dot: any, eta: string): string {
  const c = color(dot)
  return `<div style="font-family:Inter,sans-serif;padding:4px;min-width:180px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
      <div style="width:32px;height:32px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;">${dot.name.slice(0,2).toUpperCase()}</div>
      <div><p style="font-weight:700;font-size:13px;color:#1e293b;margin:0;">${dot.name}</p><p style="font-size:11px;color:#94a3b8;margin:0;">${dot.vehicle||""}</p></div>
    </div>
    <div style="background:#f8fafc;border-radius:8px;padding:8px;border:1px solid #e2e8f0;">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="font-size:11px;color:#64748b;">Order</span><span style="font-size:11px;font-weight:700;color:#15803d;font-family:monospace;">${dot.order||"—"}</span></div>
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="font-size:11px;color:#64748b;">ETA</span><span style="font-size:11px;font-weight:700;color:#3b82f6;">${eta}</span></div>
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="font-size:11px;color:#64748b;">Speed</span><span style="font-size:11px;font-weight:600;color:#1e293b;">${dot.speed||"—"}</span></div>
      <div style="display:flex;justify-content:space-between;"><span style="font-size:11px;color:#64748b;">Status</span><span style="font-size:11px;font-weight:700;color:${c};">${dot.delayed?"Delayed ⚠":dot.status.replace("_"," ")}</span></div>
    </div></div>`
}

function drawRoute(dot: any) {
  clearRoute()
  if (!gmap) return
  const g = (window as any).google
  const dPos = new g.maps.LatLng(dot.lat ?? PP.lat, dot.lng ?? PP.lng)
  const dest = new g.maps.LatLng((dot.lat ?? PP.lat) + 0.008, (dot.lng ?? PP.lng) + 0.010)

  // Use AdvancedMarkerElement for the destination pin (google.maps.Marker is deprecated)
  const AdvancedMarker = g.maps.marker?.AdvancedMarkerElement
  if (AdvancedMarker) {
    const pinEl = document.createElement('div')
    pinEl.style.cssText = `
      width:20px; height:20px; border-radius:50%;
      background:#ef4444; border:3px solid #fff;
      box-shadow:0 2px 6px rgba(0,0,0,0.4);
    `
    destinationMarker = new AdvancedMarker({
      position: { lat: dest.lat(), lng: dest.lng() },
      map: gmap,
      content: pinEl,
      title: 'Delivery destination',
      zIndex: 50,
    })
  } else {
    // Fallback for environments where AdvancedMarkerElement isn't available
    destinationMarker = new g.maps.Marker({
      position: dest, map: gmap,
      icon: { path: g.maps.SymbolPath.CIRCLE, scale: 10, fillColor: '#ef4444', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2.5 },
      title: 'Delivery destination', zIndex: 50,
    })
  }

  routePolyline = new g.maps.Polyline({ path: [dPos, dest], map: gmap, strokeColor: "#1a73e8", strokeOpacity: 0.85, strokeWeight: 3, geodesic: true })
  const b = new g.maps.LatLngBounds(); b.extend(dPos); b.extend(dest)
  gmap.fitBounds(b, { top: 80, right: 80, bottom: 80, left: 80 })
}

function clearRoute() {
  routePolyline?.setMap(null)
  routePolyline = null
  if (destinationMarker) {
    // AdvancedMarkerElement uses .map = null; legacy Marker uses .setMap(null)
    if (typeof destinationMarker.setMap === 'function') destinationMarker.setMap(null)
    else destinationMarker.map = null
    destinationMarker = null
  }
}

function selectDriver(dot: any) {
  if (selectedDriver.value?.id === dot.id) { clearSelection(); return }
  selectedDriver.value = dot
  if (gmap) {
    const entry = driverMarkers.get(dot.id)
    if (entry) openInfoWindow(dot, entry.marker)
    else drawRoute(dot)
  }
}

function clearSelection() {
  selectedDriver.value = null
  infoWindow?.close()
  clearRoute()
  if (gmap) gmap.setCenter(PP)
}

function applyFilters() {
  if (!gmap) return
  for (const dot of allDots.value) {
    const entry = driverMarkers.get(dot.id)
    if (!entry) continue
    const visible = filteredDots.value.some((d: any) => d.id === dot.id)
    // AdvancedMarkerElement uses .map = null to hide
    if (typeof entry.marker.setVisible === 'function') {
      entry.marker.setVisible(visible)
    } else {
      entry.marker.map = visible ? gmap : null
    }
    entry.trail.setVisible(visible)
  }
}

async function refresh() {
  refreshing.value = true
  try {
    await driversStore.fetchLiveLocations()
    if (gmap) {
      for (const dot of allDots.value) upsertMarker(dot)
      const ids = new Set(allDots.value.map((d: any) => d.id))
      for (const [id, { marker, trail }] of driverMarkers) {
        if (!ids.has(id)) { marker.setMap(null); trail.setMap(null); driverMarkers.delete(id) }
      }
    }
    lastRefresh.value = new Date().toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit", second: "2-digit" })
  } finally { refreshing.value = false }
}

function manualRefresh() { refresh() }

function handleVisibility() {
  if (document.hidden) { if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null } }
  else { refresh(); refreshTimer = setInterval(refresh, 15000) }
}

onMounted(async () => {
  try {
    await loadGoogleMaps()
  } catch (e) { console.error("[Map] Load failed:", e) }
  await new Promise(r => setTimeout(r, 150))
  if (isMapsLoaded() && mapEl.value) initMap()
  await refresh()
  if (gmap) for (const dot of allDots.value) upsertMarker(dot)
  refreshTimer = setInterval(refresh, 15000)
  document.addEventListener("visibilitychange", handleVisibility)
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
  document.removeEventListener('visibilitychange', handleVisibility)
  for (const { marker, trail } of driverMarkers.values()) {
    // AdvancedMarkerElement: set map to null; legacy: setMap(null)
    if (typeof marker.setMap === 'function') marker.setMap(null)
    else marker.map = null
    trail.setMap(null)
  }
  driverMarkers.clear(); clearRoute(); infoWindow?.close()
})
</script>
