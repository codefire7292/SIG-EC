<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { AcademicCapIcon, CheckBadgeIcon, SparklesIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    show: Boolean,
    title: String,
    message: String,
    certificateUuid: String
})

const emit = defineEmits(['close'])

function handleClose() {
    if (props.certificateUuid && window.markCertificateAsCelebrated) {
        window.markCertificateAsCelebrated(props.certificateUuid)
    }
    emit('close')
}

const canvasRef = ref(null)
let animationId = null

const confettiCount = 150
const pieces = []

class ConfettiPiece {
    constructor(canvasWidth, canvasHeight) {
        this.w = canvasWidth
        this.h = canvasHeight
        this.x = Math.random() * this.w
        this.y = Math.random() * this.h - this.h
        this.r = Math.random() * 6 + 4
        this.d = Math.random() * confettiCount
        this.color = `hsl(${Math.random() * 360}, 100%, 50%)`
        this.tilt = Math.random() * 10 - 10
        this.tiltAngleIncremental = Math.random() * 0.07 + 0.05
        this.tiltAngle = 0
    }

    draw(ctx) {
        ctx.beginPath()
        ctx.lineWidth = this.r / 2
        ctx.strokeStyle = this.color
        ctx.moveTo(this.x + this.tilt + (this.r / 4), this.y)
        ctx.lineTo(this.x + this.tilt, this.y + this.tilt + (this.r / 4))
        ctx.stroke()
    }

    update() {
        this.tiltAngle += this.tiltAngleIncremental
        this.y += (Math.cos(this.d) + 3 + this.r / 2) / 2
        this.tilt = Math.sin(this.tiltAngle) * 15

        if (this.y > this.h) {
            this.x = Math.random() * this.w
            this.y = -20
            this.tilt = Math.random() * 10 - 10
        }
    }
}

function initConfetti() {
    const canvas = canvasRef.value
    if (!canvas) return
    const ctx = canvas.getContext('2d')
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight

    for (let i = 0; i < confettiCount; i++) {
        pieces.push(new ConfettiPiece(canvas.width, canvas.height))
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height)
        pieces.forEach(p => {
            p.update()
            p.draw(ctx)
        })
        animationId = requestAnimationFrame(animate)
    }
    animate()
}

onMounted(() => {
    if (props.show) {
        initConfetti()
    }
})

onUnmounted(() => {
    if (animationId) {
        cancelAnimationFrame(animationId)
    }
})
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden">
            <!-- Backdrop -->
            <div 
                class="absolute inset-0 bg-black/80 backdrop-blur-xl transition-opacity animate-in fade-in duration-700" 
                @click="handleClose"
            ></div>

            <!-- Confetti Canvas -->
            <canvas 
                ref="canvasRef"
                class="absolute inset-0 pointer-events-none z-10"
            ></canvas>

            <!-- Content Card -->
            <div class="relative z-20 max-w-lg w-full mx-4 transform animate-in zoom-in-95 slide-in-from-bottom-10 duration-700">
                <div class="bg-slate-900 border border-blue-500/30 rounded-[3rem] p-12 text-center shadow-2xl shadow-blue-500/20 relative overflow-hidden group">
                    
                    <!-- Futuristic Background Elements -->
                    <div class="absolute inset-0 pointer-events-none">
                        <div class="absolute -right-20 -top-20 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>
                        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-cyan-600/10 rounded-full blur-3xl"></div>
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                    </div>

                    <!-- Glowing Seal -->
                    <div class="relative inline-flex mb-8">
                        <div class="absolute inset-0 bg-blue-500 blur-2xl opacity-20 animate-pulse"></div>
                        <div class="w-32 h-32 bg-gradient-to-br from-blue-600 via-indigo-600 to-cyan-600 rounded-[2.5rem] flex items-center justify-center text-white shadow-2xl rotate-3 group-hover:rotate-6 transition-transform duration-500">
                            <AcademicCapIcon class="h-16 w-16" />
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-blue-600 shadow-xl border-4 border-slate-900 animate-bounce">
                            <CheckBadgeIcon class="h-8 w-8" />
                        </div>
                    </div>

                    <!-- Texts -->
                    <div class="relative">
                        <div class="flex items-center justify-center gap-2 mb-4">
                            <SparklesIcon class="h-6 w-6 text-yellow-400 animate-spin-slow" />
                            <span class="text-blue-400 font-bold tracking-[0.3em] uppercase text-xs">Achievement Unlocked</span>
                            <SparklesIcon class="h-6 w-6 text-yellow-400 animate-spin-slow" />
                        </div>
                        
                        <h2 class="text-5xl font-black text-white mb-6 leading-tight tracking-tight">
                            {{ title }}
                        </h2>
                        
                        <p class="text-slate-400 text-lg mb-10 leading-relaxed max-w-sm mx-auto">
                            {{ message }}
                        </p>

                        <div class="flex flex-col gap-4">
                            <button 
                                @click="handleClose"
                                class="w-full py-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-xl shadow-blue-900/40 relative overflow-hidden group/btn"
                            >
                                <span class="relative z-10">C'est incroyable ! Merci</span>
                                <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                            </button>
                            
                            <p class="text-slate-500 text-xs font-medium italic">
                                Votre attestation est maintenant disponible dans votre tableau de bord.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 8s linear infinite;
}
</style>
