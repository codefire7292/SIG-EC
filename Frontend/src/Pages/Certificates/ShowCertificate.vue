<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { 
    AcademicCapIcon, 
    CheckBadgeIcon, 
    ArrowDownTrayIcon,
    ShareIcon,
    GlobeAltIcon,
    ShieldCheckIcon,
    UserIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    certificate: Object,
    verify_url: String 
})

function shareOnLinkedIn() {
    const url = window.location.href
    const title = `Certification E-CRE : ${props.certificate.module_title}`
    const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`
    window.open(linkedinUrl, '_blank')
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href)
    alert('Lien de vérification copié !')
}
</script>

<template>
    <Head :title="`CERTIFICATION OFFICIELLE - ${certificate.student_name}`" />

    <div class="min-h-screen bg-[#020617] text-white selection:bg-cyan-500/30 selection:text-cyan-200 font-sans overflow-x-hidden relative flex flex-col items-center justify-center py-12 px-6">
        
        <!-- Background Ornaments -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute top-0 left-0 w-full h-full opacity-20" style="background-image: radial-gradient(circle at 2px 2px, #334155 1px, transparent 0); background-size: 30px 30px;"></div>
            <div class="absolute top-1/4 left-1/4 w-[600px] h-[600px] bg-cyan-500/10 rounded-full blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s"></div>
            
            <!-- Tech Grid -->
            <div class="absolute inset-0 opacity-[0.03]" 
                style="background-image: linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 100px 100px;">
            </div>
        </div>

        <!-- Floating Controls -->
        <div class="fixed top-8 right-8 z-50 flex gap-4">
            <button @click="copyLink" class="p-4 bg-slate-900/80 backdrop-blur-2xl border border-white/10 hover:border-cyan-500 rounded-2xl transition-all group shadow-2xl">
                <ShareIcon class="w-6 h-6 text-slate-400 group-hover:text-cyan-400" />
            </button>
            <a :href="route('certificates.download', certificate.id)" class="flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-500 hover:to-blue-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-cyan-900/40 transition-all active:scale-95">
                <ArrowDownTrayIcon class="w-5 h-5" />
                Télécharger PDF
            </a>
        </div>

        <!-- Main Certificate Container -->
        <div class="relative w-full max-w-[1100px] aspect-[1.414/1] z-10 group animate-in fade-in zoom-in duration-1000">
            
            <!-- Outer Glow -->
            <div class="absolute -inset-10 bg-gradient-to-r from-amber-500/10 via-cyan-500/20 to-blue-600/10 rounded-[4rem] blur-[100px] opacity-40 group-hover:opacity-60 transition-opacity duration-1000"></div>
            
            <!-- The Frame System -->
            <div class="relative h-full w-full bg-[#020617] rounded-[3.5rem] overflow-hidden border-[1px] border-white/10 shadow-[0_0_100px_rgba(0,0,0,1)] flex flex-col items-center justify-center">
                
                <!-- Background Texture -->
                <div class="absolute inset-0 pointer-events-none opacity-[0.05]" 
                    style="background-image: linear-gradient(0deg, transparent 24%, rgba(255, 255, 255, .05) 25%, rgba(255, 255, 255, .05) 26%, transparent 27%, transparent 74%, rgba(255, 255, 255, .05) 75%, rgba(255, 255, 255, .05) 76%, transparent 77%, transparent), linear-gradient(90deg, transparent 24%, rgba(255, 255, 255, .05) 25%, rgba(255, 255, 255, .05) 26%, transparent 27%, transparent 74%, rgba(255, 255, 255, .05) 75%, rgba(255, 255, 255, .05) 76%, transparent 77%, transparent); background-size: 50px 50px;">
                </div>

                <!-- Main Ornamental Borders -->
                <div class="absolute inset-6 border-[3px] border-amber-500/30 rounded-[3rem] pointer-events-none"></div>
                <div class="absolute inset-10 border-[1px] border-cyan-500/20 rounded-[2.5rem] pointer-events-none"></div>
                
                <!-- Corner Ornaments (SVG) -->
                <div v-for="pos in ['top-0 left-0', 'top-0 right-0 rotate-90', 'bottom-0 left-0 -rotate-90', 'bottom-0 right-0 rotate-180']" 
                    :key="pos"
                    :class="['absolute w-48 h-48 pointer-events-none', pos]"
                >
                    <svg viewBox="0 0 200 200" class="w-full h-full opacity-60">
                        <path d="M 0 0 L 200 0 L 200 10 L 10 10 L 10 200 L 0 200 Z" fill="url(#goldGradient)" />
                        <path d="M 20 20 L 150 20 L 150 25 L 25 25 L 25 150 L 20 150 Z" fill="url(#cyanGradient)" />
                        <rect x="0" y="0" width="20" height="20" fill="url(#goldGradient)" />
                        <circle cx="20" cy="20" r="4" fill="#fbbf24" />
                        <defs>
                            <linearGradient id="goldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#fbbf24;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="cyanGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#22d3ee;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#0891b2;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>

                <!-- Content Area -->
                <div class="relative z-20 flex-1 w-full flex flex-col items-center justify-between py-24 px-20 text-center">
                    
                    <!-- Header -->
                    <div class="space-y-6">
                        <div class="relative inline-flex items-center justify-center p-6 bg-white/5 rounded-[2rem] border border-white/10 shadow-2xl group">
                            <div class="absolute inset-0 bg-cyan-500 blur-3xl opacity-0 group-hover:opacity-20 transition-opacity duration-1000"></div>
                            <AcademicCapIcon class="w-16 h-16 text-cyan-400 mb-2" />
                            <div class="absolute -bottom-2 px-4 py-1 bg-amber-500 rounded-lg text-[8px] font-black uppercase tracking-widest text-black">OFFICIAL</div>
                        </div>
                        
                        <div class="space-y-2">
                            <h1 class="text-6xl font-black tracking-[0.4em] text-white uppercase sm:text-7xl leading-none">
                                Certification
                            </h1>
                            <div class="flex items-center justify-center gap-4">
                                <div class="h-px w-12 bg-amber-500/50"></div>
                                <p class="text-amber-400 font-bold tracking-[0.6em] uppercase text-[10px]">Quantum Nexus Institute</p>
                                <div class="h-px w-12 bg-amber-500/50"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient -->
                    <div class="w-full relative">
                        <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-48 bg-gradient-to-r from-transparent via-cyan-500/5 to-transparent blur-3xl pointer-events-none"></div>
                        
                        <div class="relative space-y-8">
                            <p class="text-slate-500 font-black uppercase tracking-[0.5em] text-[10px]">Ce document certifie officiellement que</p>
                            
                            <div class="relative inline-block py-6 px-16 bg-white/5 backdrop-blur-xl rounded-[2.5rem] border border-white/10 shadow-[0_0_50px_rgba(34,211,238,0.1)] group/name">
                                <h2 class="text-6xl sm:text-8xl font-black text-transparent bg-clip-text bg-gradient-to-b from-white via-white to-slate-500 uppercase tracking-tighter transition-all duration-700 group-hover/name:scale-105">
                                    {{ certificate.student_name }}
                                </h2>
                                <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-48 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent"></div>
                            </div>

                            <div class="space-y-4">
                                <p class="text-slate-500 font-black uppercase tracking-[0.5em] text-[10px]">A validé avec distinction le module</p>
                                <h3 class="text-3xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-600 uppercase tracking-tight italic">
                                    {{ certificate.module_title }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Verification -->
                    <div class="w-full grid grid-cols-3 items-end gap-16 relative">
                        <!-- Student Photo Identity / Signature Left -->
                        <div class="flex flex-col items-center space-y-4 group/sig">
                            <div class="relative w-32 h-32 flex items-center justify-center p-2 bg-white/5 rounded-[2rem] border border-white/10 shadow-2xl transition-all group-hover/sig:scale-105 overflow-hidden">
                                <img v-if="certificate.profile_photo_url" :src="certificate.profile_photo_url" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover/sig:opacity-100 transition-opacity">
                                <UserIcon v-else class="w-12 h-12 text-slate-700" />
                                
                                <div class="absolute inset-0 border border-cyan-500/20 rounded-[2rem]"></div>
                                <div class="absolute inset-2 border border-amber-500/10 rounded-[1.5rem]"></div>
                                
                                <!-- ID Badge Overlay -->
                                <div class="absolute bottom-0 inset-x-0 py-1 bg-cyan-600/80 backdrop-blur-md text-[6px] font-black tracking-widest uppercase text-white">Verified ID</div>
                            </div>
                            <div class="w-32 h-[2px] bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">IDENTITÉ VÉRIFIÉE</span>
                                <span class="text-[7px] text-slate-600 font-bold uppercase mt-1">E-CRE Digital DNA</span>
                            </div>
                        </div>
                        
                        <!-- Central Medallion & QR -->
                        <div class="relative flex flex-col items-center group/qr">
                            <!-- Large Central Medallion (Behind QR) -->
                            <div class="absolute -top-12 w-48 h-48 bg-cyan-500/10 rounded-full blur-3xl animate-pulse"></div>
                            
                            <div class="relative z-10">
                                <div class="relative p-4 bg-white rounded-[2rem] shadow-[0_0_80px_rgba(34,211,238,0.2)] transition-transform duration-700 group-hover/qr:rotate-[360deg] cursor-pointer flex items-center justify-center" @click="copyLink">
                                    <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(verify_url)}&ecc=H`" class="w-24 h-24 mix-blend-multiply">
                                    
                                    <!-- Embedded Logo Overlay -->
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-8 h-8 bg-white rounded-lg p-[2px] shadow-sm border border-gray-100">
                                            <img src="/images/logo-cre.png" class="w-full h-full object-contain">
                                        </div>
                                    </div>

                                    <!-- Medallion Ring -->
                                    <div class="absolute -inset-4 border-2 border-dashed border-amber-500/30 rounded-full animate-[spin_20s_linear_infinite]"></div>
                                    <div class="absolute -inset-8 border border-cyan-500/20 rounded-full animate-[spin_15s_linear_infinite_reverse]"></div>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex flex-col items-center">
                                <span class="text-[10px] font-black text-white uppercase tracking-[0.6em]">SCELLÉ DIGITAL</span>
                                <span class="text-[7px] text-cyan-500 font-black uppercase mt-1 tracking-widest">ID: QN-{{ certificate.uuid.substring(0, 8) }}</span>
                            </div>
                        </div>

                        <!-- Signature Right / Date -->
                        <div class="flex flex-col items-center space-y-4">
                            <div class="relative w-32 h-16 flex items-center justify-center">
                                <span class="font-signature text-3xl text-amber-500/80 rotate-2 select-none">Registrar Office</span>
                                <div class="absolute -left-4 top-0 w-8 h-8 rounded-full border border-amber-500/50 flex items-center justify-center bg-amber-500/5 shadow-[0_0_10px_rgba(245,158,11,0.3)]">
                                    <span class="text-[6px] font-black text-amber-400">OFFIC</span>
                                </div>
                            </div>
                            <div class="w-32 h-[2px] bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">DÉLIVRÉ LE</span>
                                <span class="text-[8px] text-amber-500 font-black uppercase mt-1 tracking-widest">{{ certificate.issued_at_fr }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final Polish: Scanner Beam -->
                <div class="absolute inset-x-0 h-[2px] bg-cyan-500/30 blur-sm pointer-events-none z-30 animate-scan shadow-[0_0_15px_rgba(34,211,238,0.5)]"></div>
            </div>
            
            <!-- Watermark UUID -->
            <div class="absolute -bottom-12 left-1/2 -translate-x-1/2 text-[10px] font-black text-slate-700 tracking-[0.5em] uppercase opacity-40">
                SYSTEM-AUTH-TOKEN // {{ certificate.uuid.substring(0, 18) }}...
            </div>
        </div>

        <nav class="mt-24 flex items-center gap-16 px-12 py-6 bg-white/5 backdrop-blur-xl border border-white/10 rounded-[2rem] text-slate-500 shadow-2xl">
            <div class="flex items-center gap-3">
                <ShieldCheckIcon class="w-5 h-5 text-amber-500" />
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Blockchain Verified</span>
            </div>
            <div class="h-4 w-px bg-white/10"></div>
            <div class="flex items-center gap-3">
                <GlobeAltIcon class="w-5 h-5 text-cyan-500" />
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Public Ledger Access</span>
            </div>
            <div class="h-4 w-px bg-white/10"></div>
            <div class="flex items-center gap-3">
                <CheckBadgeIcon class="w-5 h-5 text-emerald-500" />
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">E-CRE Official Seal</span>
            </div>
        </nav>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;700&family=Alex+Brush&display=swap');

.font-sans {
    font-family: 'Space Grotesk', sans-serif;
}

.font-signature {
    font-family: 'Alex Brush', cursive;
}

h1, h2, h3 {
    text-shadow: 0 0 40px rgba(0,0,0,1);
}

@keyframes scan {
    0% { top: 0%; opacity: 0; }
    50% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}

.animate-scan {
    animation: scan 4s linear infinite;
}

/* Texture overlay */
.min-h-screen::before {
    content: '';
    position: fixed;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
    opacity: 0.05;
    pointer-events: none;
    z-index: 1;
}
</style>
