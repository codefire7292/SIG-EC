<script setup>
/**
 * AssetCheckout.vue
 * Composant Vue 3 (Composition API) pour l'emprunt d'équipement
 * avec capture de signature numérique via signature_pad.
 */
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useForm } from '@inertiajs/vue3'
import SignaturePad from 'signature_pad'

const props = defineProps({
    /** Liste des équipements disponibles */
    availableAssets: {
        type: Array,
        required: true,
    },
})

// -----------------------------------------------------------------------
// Refs
// -----------------------------------------------------------------------
const canvasRef = ref(null)
let signaturePad = null

const form = useForm({
    asset_id: '',
    signature: '',
})

// -----------------------------------------------------------------------
// Lifecycle
// -----------------------------------------------------------------------
onMounted(() => {
    if (canvasRef.value) {
        signaturePad = new SignaturePad(canvasRef.value, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
        })
        resizeCanvas()
        window.addEventListener('resize', resizeCanvas)
    }
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', resizeCanvas)
})

// -----------------------------------------------------------------------
// Methods
// -----------------------------------------------------------------------
function resizeCanvas() {
    if (!canvasRef.value) return
    const ratio = Math.max(window.devicePixelRatio || 1, 1)
    const canvas = canvasRef.value
    canvas.width = canvas.offsetWidth * ratio
    canvas.height = canvas.offsetHeight * ratio
    canvas.getContext('2d').scale(ratio, ratio)
    signaturePad?.clear()
}

function clearSignature() {
    signaturePad?.clear()
    form.signature = ''
}

function submit() {
    if (!signaturePad || signaturePad.isEmpty()) {
        alert('Veuillez signer avant de soumettre.')
        return
    }

    form.signature = signaturePad.toDataURL('image/png')
    form.post(route('loans.checkout'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            clearSignature()
        },
    })
}
</script>

<template>
    <div class="asset-checkout">
        <h2 class="text-xl font-semibold mb-4">Emprunt d'équipement</h2>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Asset Selection -->
            <div>
                <label for="asset_id" class="block text-sm font-medium text-gray-700">
                    Équipement
                </label>
                <select
                    id="asset_id"
                    v-model="form.asset_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                           focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required
                >
                    <option value="" disabled>-- Sélectionnez un équipement --</option>
                    <option
                        v-for="asset in availableAssets"
                        :key="asset.id"
                        :value="asset.id"
                    >
                        {{ asset.nom }} — {{ asset.serie || 'N/A' }}
                    </option>
                </select>
                <p v-if="form.errors.asset_id" class="mt-1 text-sm text-red-600">
                    {{ form.errors.asset_id }}
                </p>
            </div>

            <!-- Signature Pad -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Signature numérique
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-1 bg-white">
                    <canvas
                        ref="canvasRef"
                        class="w-full"
                        style="height: 200px; touch-action: none;"
                    />
                </div>
                <div class="mt-2 flex gap-2">
                    <button
                        type="button"
                        @click="clearSignature"
                        class="text-sm text-gray-500 hover:text-gray-700 underline"
                    >
                        Effacer la signature
                    </button>
                </div>
                <p v-if="form.errors.signature" class="mt-1 text-sm text-red-600">
                    {{ form.errors.signature }}
                </p>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center px-6 py-2.5 bg-indigo-600 text-white
                           font-medium text-sm rounded-lg shadow-sm
                           hover:bg-indigo-700 focus:outline-none focus:ring-2
                           focus:ring-indigo-500 focus:ring-offset-2
                           disabled:opacity-50 disabled:cursor-not-allowed
                           transition-colors duration-200"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ form.processing ? 'Enregistrement...' : 'Confirmer l\'emprunt' }}
                </button>
            </div>
        </form>
    </div>
</template>
