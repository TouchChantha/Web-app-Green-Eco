<template>
  <div class="space-y-5">

    <!-- Back -->
    <RouterLink to="/orders" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-[#3d9a3d] transition-colors">
      <ArrowLeftIcon class="w-4 h-4" />
      {{ t.backToOrders }}
    </RouterLink>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">

      <!-- ── Create Order Form ─────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 class="text-base font-semibold text-slate-800">{{ t.createOrder }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ t.createOrderDesc }}</p>
          </div>
          <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-semibold">{{ t.admin }}</span>
        </div>

        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">

          <!-- Recipient name -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
              {{ t.recipientName }} <span class="text-red-500">*</span>
            </label>
            <input v-model="form.recipient_name" required placeholder="Sokha Lim"
              class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] focus:border-transparent transition-all" />
          </div>

          <!-- Recipient phone -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
              {{ t.recipientPhone }} <span class="text-red-500">*</span>
            </label>
            <input v-model="form.recipient_phone" required placeholder="+855 12 999 888"
              class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] focus:border-transparent transition-all" />
          </div>

          <!-- Pickup address -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
              {{ t.pickupAddress }} <span class="text-red-500">*</span>
              <span class="text-slate-400 font-normal">{{ t.clickMapOrType }}</span>
            </label>
            <div class="relative">
              <MapPinIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-400" />
              <input v-model="form.pickup_address" required
                :placeholder="t.pickupAddress"
                @blur="geocodeAddress('pickup')"
                @keydown.enter.prevent="geocodeAddress('pickup')"
                @input="form.pickup_lat = null; form.pickup_lng = null"
                class="w-full pl-9 pr-4 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] focus:border-transparent transition-all"
                :class="{ 'border-blue-400 ring-2 ring-blue-200': pickingMode === 'pickup' }" />
            </div>
            <p v-if="form.pickup_lat" class="text-xs text-slate-400 mt-1">
              📍 {{ form.pickup_lat?.toFixed(6) }}, {{ form.pickup_lng?.toFixed(6) }}
            </p>
          </div>

          <!-- Delivery address -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
              {{ t.deliveryAddress }} <span class="text-red-500">*</span>
              <span class="text-slate-400 font-normal">{{ t.clickMapOrType }}</span>
            </label>
            <div class="relative">
              <MapPinIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#3d9a3d]" />
              <input v-model="form.delivery_address" required
                :placeholder="t.deliveryAddress"
                @blur="geocodeAddress('delivery')"
                @keydown.enter.prevent="geocodeAddress('delivery')"
                @input="form.delivery_lat = null; form.delivery_lng = null"
                class="w-full pl-9 pr-4 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] focus:border-transparent transition-all"
                :class="{ 'border-green-400 ring-2 ring-green-200': pickingMode === 'delivery' }" />
            </div>
            <p v-if="form.delivery_lat" class="text-xs text-slate-400 mt-1">
              📍 {{ form.delivery_lat?.toFixed(6) }}, {{ form.delivery_lng?.toFixed(6) }}
            </p>
          </div>

          <!-- Priority + Scheduled at -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ t.priority }}</label>
              <select v-model="form.priority"
                class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] bg-white">
                <option value="low">{{ t.low }}</option>
                <option value="normal">{{ t.normal }}</option>
                <option value="high">{{ t.high }}</option>
                <option value="urgent">{{ t.urgent }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ t.scheduledAt }}</label>
              <input v-model="form.scheduled_at" type="datetime-local"
                class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ t.notes }}</label>
            <input v-model="form.notes" :placeholder="t.notes"
              class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] focus:border-transparent transition-all" />
          </div>

          <!-- Error -->
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 flex items-center gap-2">
            <ExclamationCircleIcon class="w-4 h-4 shrink-0" />
            {{ error }}
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-2">
            <RouterLink to="/orders"
              class="flex-1 text-center px-4 py-2.5 border border-slate-300 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
              {{ t.cancel }}
            </RouterLink>
            <button type="submit" :disabled="submitting"
              class="flex-1 bg-[#0d3320] hover:bg-[#1a4731] disabled:opacity-60 text-white font-semibold py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm">
              <svg v-if="submitting" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ submitting ? t.creating : t.createOrderBtn }}
            </button>
          </div>
        </form>
      </div>

      <!-- ── Real Google Map ───────────────────────────────────────────── -->
      <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
          <!-- Map header with pin mode buttons -->
          <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2">
              <MapIcon class="w-4 h-4 text-[#3d9a3d]" />
              <h3 class="text-sm font-semibold text-slate-700">{{ t.mapPinPreview }}</h3>
            </div>
            <div class="flex gap-2">
              <button type="button"
                @click="setPickingMode('pickup')"
                :class="[
                  'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border',
                  pickingMode === 'pickup'
                    ? 'bg-blue-500 text-white border-blue-500 shadow-sm'
                    : 'bg-white text-blue-600 border-blue-300 hover:bg-blue-50'
                ]">
                <span class="w-2.5 h-2.5 rounded-full bg-current opacity-80"></span>
                {{ t.pickup }}
              </button>
              <button type="button"
                @click="setPickingMode('delivery')"
                :class="[
                  'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border',
                  pickingMode === 'delivery'
                    ? 'bg-[#3d9a3d] text-white border-[#3d9a3d] shadow-sm'
                    : 'bg-white text-[#3d9a3d] border-[#3d9a3d] hover:bg-green-50'
                ]">
                <span class="w-2.5 h-2.5 rounded-full bg-current opacity-80"></span>
                {{ t.delivery }}
              </button>
            </div>
          </div>

          <!-- Instruction banner -->
          <div v-if="pickingMode" class="px-5 py-2 text-xs font-medium flex items-center gap-2"
            :class="pickingMode === 'pickup' ? 'bg-blue-50 text-blue-700' : 'bg-green-50 text-green-700'">
            <span>{{ pickingMode === 'pickup' ? '🔵' : '🟢' }}</span>
            {{ pickingMode === 'pickup'
              ? (lang === 'km' ? 'ចុចផែនទីដើម្បីដាក់ទីតាំងទទួល' : 'Click map to place pickup pin')
              : (lang === 'km' ? 'ចុចផែនទីដើម្បីដាក់ទីតាំងដឹក' : 'Click map to place delivery pin')
            }}
          </div>

          <!-- Map container -->
          <div ref="mapEl" style="height: 380px; width: 100%;"></div>

          <!-- Legend -->
          <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex gap-4">
            <div class="flex items-center gap-2 text-xs text-slate-500">
              <span class="w-3 h-3 rounded-full bg-blue-500"></span> {{ t.pickup }}
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500">
              <span class="w-3 h-3 rounded-full bg-[#3d9a3d]"></span> {{ t.delivery }}
            </div>
            <div v-if="geocoding" class="ml-auto flex items-center gap-1.5 text-xs text-slate-400">
              <svg class="animate-spin w-3 h-3" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ lang === 'km' ? 'កំពុងស្វែងរកអាសយដ្ឋាន...' : 'Getting address...' }}
            </div>
          </div>
        </div>

        <!-- Order summary preview -->
        <div v-if="form.recipient_name || form.pickup_address" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
          <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ t.orderPreview }}</h3>
          <div class="space-y-2 text-sm">
            <div v-if="form.recipient_name" class="flex justify-between">
              <span class="text-slate-500">{{ t.recipient }}</span>
              <span class="font-medium text-slate-800">{{ form.recipient_name }}</span>
            </div>
            <div v-if="form.recipient_phone" class="flex justify-between">
              <span class="text-slate-500">{{ t.phone }}</span>
              <span class="font-medium text-slate-800">{{ form.recipient_phone }}</span>
            </div>
            <div v-if="form.priority" class="flex justify-between">
              <span class="text-slate-500">{{ t.priority }}</span>
              <PriorityBadge :priority="form.priority" />
            </div>
            <div v-if="form.scheduled_at" class="flex justify-between">
              <span class="text-slate-500">{{ t.scheduled }}</span>
              <span class="font-medium text-slate-800 text-xs">{{ form.scheduled_at }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useOrdersStore } from '../../stores/orders'
import PriorityBadge from '../../components/ui/PriorityBadge.vue'
import { ArrowLeftIcon, MapPinIcon, MapIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import { loadGoogleMaps, isMapsLoaded } from '../../composables/useGoogleMaps'
import { useI18n } from '../../composables/useI18n'

const store = useOrdersStore()
const router = useRouter()
const { t, lang } = useI18n()

const submitting = ref(false)
const error = ref('')
const geocoding = ref(false)
const mapEl = ref<HTMLElement | null>(null)
const pickingMode = ref<'pickup' | 'delivery' | null>('pickup')

const form = reactive({
  recipient_name:   '',
  recipient_phone:  '',
  pickup_address:   '',
  pickup_lat:       null as number | null,
  pickup_lng:       null as number | null,
  delivery_address: '',
  delivery_lat:     null as number | null,
  delivery_lng:     null as number | null,
  priority:         'normal',
  scheduled_at:     '',
  notes:            '',
})

// ── Google Maps state ──────────────────────────────────────────────────────
let gmap: any = null
let clickListener: any = null
let pickupMarker: any = null
let deliveryMarker: any = null
let routeLine: any = null

const PP = { lat: 11.5564, lng: 104.9282 }

function setPickingMode(mode: 'pickup' | 'delivery') {
  pickingMode.value = pickingMode.value === mode ? null : mode
}

function makeMarkerEl(type: 'pickup' | 'delivery'): HTMLElement {
  const el = document.createElement('div')
  const color = type === 'pickup' ? '#3b82f6' : '#16a34a'
  const label = type === 'pickup' ? 'P' : 'D'
  el.style.cssText = `
    width: 32px; height: 32px; border-radius: 50%;
    background: ${color}; border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.35);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff;
    font-family: Inter, sans-serif; cursor: pointer;
  `
  el.textContent = label
  return el
}

function placeMarker(type: 'pickup' | 'delivery', lat: number, lng: number) {
  const g = (window as any).google
  if (!gmap || !g?.maps) return

  const AdvancedMarker = g.maps.marker?.AdvancedMarkerElement ?? g.maps.Marker
  const pos = { lat, lng }

  if (type === 'pickup') {
    if (pickupMarker) {
      if (typeof pickupMarker.setMap === 'function') pickupMarker.setMap(null)
      else pickupMarker.map = null
    }
    pickupMarker = new AdvancedMarker({
      position: pos, map: gmap,
      content: makeMarkerEl('pickup'),
      title: 'Pickup',
      zIndex: 10,
    })
  } else {
    if (deliveryMarker) {
      if (typeof deliveryMarker.setMap === 'function') deliveryMarker.setMap(null)
      else deliveryMarker.map = null
    }
    deliveryMarker = new AdvancedMarker({
      position: pos, map: gmap,
      content: makeMarkerEl('delivery'),
      title: 'Delivery',
      zIndex: 10,
    })
  }

  drawRouteLine()
}

function drawRouteLine() {
  const g = (window as any).google
  if (!gmap || !g?.maps) return
  if (routeLine) { routeLine.setMap(null); routeLine = null }
  if (!pickupMarker || !deliveryMarker) return

  const pPos = pickupMarker.position
  const dPos = deliveryMarker.position
  if (!pPos || !dPos) return

  routeLine = new g.maps.Polyline({
    path: [
      { lat: pPos.lat ?? pPos.lat(), lng: pPos.lng ?? pPos.lng() },
      { lat: dPos.lat ?? dPos.lat(), lng: dPos.lng ?? dPos.lng() },
    ],
    map: gmap,
    strokeColor: '#64748b',
    strokeOpacity: 0.6,
    strokeWeight: 2,
    geodesic: true,
    icons: [{
      icon: { path: 'M 0,-1 0,1', strokeOpacity: 0.8, scale: 3 },
      offset: '0', repeat: '12px',
    }],
  })

  // Fit bounds to show both pins
  const bounds = new g.maps.LatLngBounds()
  bounds.extend(pPos)
  bounds.extend(dPos)
  gmap.fitBounds(bounds, { top: 60, right: 60, bottom: 60, left: 60 })
}

async function reverseGeocode(lat: number, lng: number): Promise<string> {
  const g = (window as any).google
  if (!g?.maps?.Geocoder) return `${lat.toFixed(6)}, ${lng.toFixed(6)}`
  return new Promise((resolve) => {
    const geocoder = new g.maps.Geocoder()
    geocoder.geocode({ location: { lat, lng } }, (results: any[], status: string) => {
      if (status === 'OK' && results[0]) {
        resolve(results[0].formatted_address)
      } else {
        resolve(`${lat.toFixed(6)}, ${lng.toFixed(6)}`)
      }
    })
  })
}

/** Forward geocode: typed address → lat/lng + place pin on map */
async function geocodeAddress(type: 'pickup' | 'delivery') {
  const address = type === 'pickup' ? form.pickup_address : form.delivery_address
  if (!address?.trim()) return

  // Skip if lat/lng already set (came from map click)
  const alreadySet = type === 'pickup' ? form.pickup_lat : form.delivery_lat
  if (alreadySet) return

  const g = (window as any).google
  if (!g?.maps?.Geocoder) return

  geocoding.value = true
  try {
    await new Promise<void>((resolve) => {
      const geocoder = new g.maps.Geocoder()
      // Bias results toward Phnom Penh
      geocoder.geocode(
        { address, region: 'KH', bounds: new g.maps.LatLngBounds(
          { lat: 11.4, lng: 104.7 }, { lat: 11.7, lng: 105.1 }
        )},
        (results: any[], status: string) => {
          if (status === 'OK' && results[0]) {
            const loc = results[0].geometry.location
            const lat: number = loc.lat()
            const lng: number = loc.lng()
            if (type === 'pickup') {
              form.pickup_lat = lat
              form.pickup_lng = lng
            } else {
              form.delivery_lat = lat
              form.delivery_lng = lng
            }
            placeMarker(type, lat, lng)
            if (gmap) gmap.panTo({ lat, lng })
          }
          resolve()
        }
      )
    })
  } finally {
    geocoding.value = false
  }
}

function initMap() {
  if (!mapEl.value) return
  const g = (window as any).google
  if (!g?.maps?.Map) return

  gmap = new g.maps.Map(mapEl.value, {
    center: PP,
    zoom: 13,
    mapId: 'DEMO_MAP_ID',
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false,
    zoomControl: true,
  })

  // Click handler
  clickListener = gmap.addListener('click', async (e: any) => {
    if (!pickingMode.value) return
    const lat: number = e.latLng.lat()
    const lng: number = e.latLng.lng()
    const mode = pickingMode.value

    placeMarker(mode, lat, lng)

    if (mode === 'pickup') {
      form.pickup_lat = lat
      form.pickup_lng = lng
      geocoding.value = true
      form.pickup_address = await reverseGeocode(lat, lng)
      geocoding.value = false
      // Auto-switch to delivery mode after placing pickup
      if (!form.delivery_lat) pickingMode.value = 'delivery'
    } else {
      form.delivery_lat = lat
      form.delivery_lng = lng
      geocoding.value = true
      form.delivery_address = await reverseGeocode(lat, lng)
      geocoding.value = false
      pickingMode.value = null
    }
  })
}

async function handleSubmit() {
  submitting.value = true
  error.value = ''
  try {
    const payload: Record<string, any> = { ...form }
    if (!payload.scheduled_at) delete payload.scheduled_at
    if (!payload.notes) delete payload.notes
    // Send null for lat/lng if not set — backend columns are now nullable
    if (!payload.pickup_lat)   payload.pickup_lat   = null
    if (!payload.pickup_lng)   payload.pickup_lng   = null
    if (!payload.delivery_lat) payload.delivery_lat = null
    if (!payload.delivery_lng) payload.delivery_lng = null

    const res = await store.createOrder(payload)
    router.push(`/orders/${res.data.id}`)
  } catch (e: any) {
    const errs = e.response?.data?.errors
    if (errs) {
      error.value = Object.values(errs).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || t.value.failedCreate
    }
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    await loadGoogleMaps()
  } catch (e) {
    console.error('[Map] Load failed:', e)
  }
  await new Promise(r => setTimeout(r, 150))
  if (isMapsLoaded() && mapEl.value) initMap()
})

onUnmounted(() => {
  if (clickListener) {
    const g = (window as any).google
    g?.maps?.event?.removeListener(clickListener)
  }
  if (pickupMarker) {
    if (typeof pickupMarker.setMap === 'function') pickupMarker.setMap(null)
    else pickupMarker.map = null
  }
  if (deliveryMarker) {
    if (typeof deliveryMarker.setMap === 'function') deliveryMarker.setMap(null)
    else deliveryMarker.map = null
  }
  if (routeLine) routeLine.setMap(null)
})
</script>
