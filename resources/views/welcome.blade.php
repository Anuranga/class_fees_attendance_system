@extends('layouts.public')

@section('title', 'Early Childhood Education & Pre-School Development Network')

@section('content')
<div class="max-w-6xl mx-auto py-4 sm:py-8 space-y-16 sm:space-y-24">

    <!-- HERO SECTION: Common Early Childhood Hub -->
    <div class="rounded-3xl bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 border border-indigo-500/20 p-6 sm:p-12 shadow-2xl relative overflow-hidden">
        <!-- Playful Background Glows -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-pink-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
            <div class="lg:col-span-7 space-y-5">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-400/30 text-indigo-300 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Early Childhood Care & Development (ECCD) Network</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight">
                    Nurturing Little Minds for a <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-pink-300 to-indigo-300">Brighter Future</span>
                </h1>

                <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-xl">
                    A collaborative early childhood education platform uniting premier preschools. Empowering children aged 1.5 to 5.5 through joyful discovery, sensory exploration, play-based learning, and foundational cognitive development.
                </p>

                <!-- Action CTAs -->
                <div class="pt-2 flex flex-wrap items-center gap-3.5">
                    <a href="{{ route('public.apply') }}" 
                       class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs sm:text-sm shadow-lg shadow-amber-500/30 flex items-center gap-2.5 transition-all hover:scale-105 cursor-pointer">
                        <i class="fa-solid fa-shapes text-base"></i>
                        <span>Apply to a Pre-School (අයදුම් කරන්න)</span>
                    </a>

                    <a href="{{ route('login') }}" 
                       class="px-5 py-3.5 rounded-2xl bg-indigo-600/30 hover:bg-indigo-600/50 text-indigo-200 font-bold text-xs sm:text-sm border border-indigo-400/30 flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-chalkboard-user text-indigo-400"></i>
                        <span>Staff Portal (ගුරු පිවිසුම)</span>
                    </a>

                    <a href="{{ route('public.lookup') }}" 
                       class="px-4 py-3.5 rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-slate-300 font-bold text-xs sm:text-sm border border-slate-700 flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-id-card text-cyan-400"></i>
                        <span>Student QR Pass</span>
                    </a>
                </div>

                <!-- Trust Points -->
                <div class="pt-4 flex flex-wrap items-center gap-4 sm:gap-6 text-xs text-slate-400 border-t border-slate-800/80">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-shield-heart text-pink-400"></i> Child-Safe Campuses</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-qrcode text-emerald-400"></i> Smart QR Attendance</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-brain text-amber-400"></i> Play-Based Growth</span>
                </div>
            </div>

            <!-- Hero Graphic Feature Card -->
            <div class="lg:col-span-5">
                <div class="rounded-3xl bg-slate-900/80 border border-slate-700/80 p-6 shadow-2xl relative space-y-4">
                    <div class="relative rounded-2xl overflow-hidden aspect-4/3 border border-slate-700/60 shadow-inner group">
                        <img src="https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=800&q=80" 
                             alt="Early Childhood Learning" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                        <div class="absolute bottom-3 left-3 right-3 text-white">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-400 text-slate-950">
                                Holistic Growth
                            </span>
                            <p class="text-xs font-bold mt-1 text-white">"Every child is a unique explorer of the world."</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-2xl bg-slate-800/60 border border-slate-700/60">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Learning Ages</span>
                            <span class="text-sm font-black text-amber-300">1.5 - 5.5 Years</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-800/60 border border-slate-700/60">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Pre-Schools Connected</span>
                            <span class="text-sm font-black text-emerald-400">{{ $schools->count() }} Campus{{ $schools->count() === 1 ? '' : 'es' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION: HOW EARLY CHILDREN LEARN & DEVELOP (With Descriptive Visuals & Age Milestones) -->
    <div id="development" class="space-y-10 scroll-mt-24">
        <div class="text-center max-w-3xl mx-auto space-y-2">
            <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-pink-500/10 text-pink-300 border border-pink-500/20">
                Child Development Milestones &bull; ළමා සංවර්ධන අදියර
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                How Young Children Learn & Grow
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Early childhood is the most rapid period of brain development. Here is how children explore, think, and develop skills across the preschool journey.
            </p>
        </div>

        <!-- 4 Age Stages Cards with Rich Descriptions and Imagery -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- STAGE 1: PLAYGROUP / TODDLERS (1.5 - 2.5 yrs) -->
            <div class="rounded-3xl bg-slate-800/50 border border-slate-700/80 p-6 shadow-xl hover:border-pink-500/40 transition-all flex flex-col justify-between group">
                <div class="space-y-4">
                    <div class="relative rounded-2xl overflow-hidden aspect-16/9 border border-slate-700/60 shadow-md">
                        <img src="https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=700&q=80" 
                             alt="Toddlers Sensory Play" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-pink-500 text-white shadow">
                                Age 1.5 - 2.5 Years
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-pink-400 uppercase tracking-wider block">Stage 1 &bull; Playgroup & Toddlers</span>
                        <h3 class="text-xl font-black text-white mt-1">Sensory Exploration & Emotional Bonding</h3>
                        <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                            Toddlers learn predominantly through their senses: touching textured materials, listening to sounds, seeing bold contrasting colors, and moving their bodies.
                        </p>
                    </div>

                    <!-- Bullet points of key traits -->
                    <div class="space-y-2 pt-2 border-t border-slate-700/60 text-xs text-slate-300">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-shapes text-pink-400 mt-0.5"></i>
                            <span><strong>Sensory Learning:</strong> Water play, soft sand, clay, building blocks, and tactile textures that ignite neural curiosity.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-heart text-rose-400 mt-0.5"></i>
                            <span><strong>Emotional Trust:</strong> Developing emotional security away from home through warm, attentive caregiver relationships.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-person-walking text-amber-400 mt-0.5"></i>
                            <span><strong>Motor Mastery:</strong> Stacking rings, rolling balls, practicing stable balance, and independent finger grasp.</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-700/60 flex items-center justify-between text-xs text-pink-300 font-semibold">
                    <span>Key Growth: Trust & Bodily Coordination</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

            <!-- STAGE 2: NURSERY (2.5 - 3.5 yrs) -->
            <div class="rounded-3xl bg-slate-800/50 border border-slate-700/80 p-6 shadow-xl hover:border-amber-500/40 transition-all flex flex-col justify-between group">
                <div class="space-y-4">
                    <div class="relative rounded-2xl overflow-hidden aspect-16/9 border border-slate-700/60 shadow-md">
                        <img src="https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=700&q=80" 
                             alt="Nursery Storytelling & Painting" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-500 text-slate-950 shadow">
                                Age 2.5 - 3.5 Years
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block">Stage 2 &bull; Nursery</span>
                        <h3 class="text-xl font-black text-white mt-1">Language Explosion & Creative Expression</h3>
                        <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                            Children enter the "curious questioner" phase. Vocabulary multiplies rapidly, and pretend play blossoms as they express ideas and make sense of their daily routines.
                        </p>
                    </div>

                    <!-- Bullet points -->
                    <div class="space-y-2 pt-2 border-t border-slate-700/60 text-xs text-slate-300">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-comments text-amber-400 mt-0.5"></i>
                            <span><strong>Language & Rhymes:</strong> Storybook circles, Sinhala & English nursery songs, and asking "Why?" about everything.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-paintbrush text-pink-400 mt-0.5"></i>
                            <span><strong>Creative Freedom:</strong> Large finger-painting strokes, playdough modeling, and expressing emotions through colors.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-users text-cyan-400 mt-0.5"></i>
                            <span><strong>Social Awareness:</strong> Transitioning from playing side-by-side to learning to share toys, take turns, and make first friends.</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-700/60 flex items-center justify-between text-xs text-amber-300 font-semibold">
                    <span>Key Growth: Expressive Speech & Imagination</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

            <!-- STAGE 3: LOWER KINDERGARTEN - LKG (3.5 - 4.5 yrs) -->
            <div class="rounded-3xl bg-slate-800/50 border border-slate-700/80 p-6 shadow-xl hover:border-cyan-500/40 transition-all flex flex-col justify-between group">
                <div class="space-y-4">
                    <div class="relative rounded-2xl overflow-hidden aspect-16/9 border border-slate-700/60 shadow-md">
                        <img src="https://images.unsplash.com/photo-1596464716127-f2a829822301?auto=format&fit=crop&w=700&q=80" 
                             alt="LKG Phonics and Blocks" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-cyan-500 text-slate-950 shadow">
                                Age 3.5 - 4.5 Years
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider block">Stage 3 &bull; Lower Kindergarten (LKG)</span>
                        <h3 class="text-xl font-black text-white mt-1">Phonics, Numbers & Cooperative Play</h3>
                        <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                            Children begin connecting spoken words to letter sounds, understanding quantities through hands-on objects, and actively collaborating with peers.
                        </p>
                    </div>

                    <!-- Bullet points -->
                    <div class="space-y-2 pt-2 border-t border-slate-700/60 text-xs text-slate-300">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-arrow-down-a-z text-cyan-400 mt-0.5"></i>
                            <span><strong>Phonics Awareness:</strong> Recognizing letter shapes, matching beginning sounds with familiar animal and object pictures.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-arrow-up-1-9 text-emerald-400 mt-0.5"></i>
                            <span><strong>Number Sense:</strong> Counting physical counters, sorting by color/shape/size, and discovering patterns in nature.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-handshake text-indigo-400 mt-0.5"></i>
                            <span><strong>Team Cooperation:</strong> Group block construction, following multi-step instructions, and empathy toward classmates.</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-700/60 flex items-center justify-between text-xs text-cyan-300 font-semibold">
                    <span>Key Growth: Logical Connections & Fine Motor Control</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

            <!-- STAGE 4: UPPER KINDERGARTEN - UKG (4.5 - 5.5 yrs) -->
            <div class="rounded-3xl bg-slate-800/50 border border-slate-700/80 p-6 shadow-xl hover:border-emerald-500/40 transition-all flex flex-col justify-between group">
                <div class="space-y-4">
                    <div class="relative rounded-2xl overflow-hidden aspect-16/9 border border-slate-700/60 shadow-md">
                        <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=700&q=80" 
                             alt="UKG School Readiness" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500 text-slate-950 shadow">
                                Age 4.5 - 5.5 Years
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block">Stage 4 &bull; Upper Kindergarten (UKG)</span>
                        <h3 class="text-xl font-black text-white mt-1">School Readiness, Confidence & Inquiry</h3>
                        <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                            Preparing for the smooth transition to primary grade school. Children develop confidence in writing, early reading, logical math concepts, and self-management.
                        </p>
                    </div>

                    <!-- Bullet points -->
                    <div class="space-y-2 pt-2 border-t border-slate-700/60 text-xs text-slate-300">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-book-open-reader text-emerald-400 mt-0.5"></i>
                            <span><strong>Early Reading & Writing:</strong> Writing their own name, blending phonetic sounds into simple words, and reading short story sentences.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-calculator text-amber-400 mt-0.5"></i>
                            <span><strong>Math & Science:</strong> Simple addition concepts with concrete manipulatives, observing plants and weather, questioning how things work.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-award text-purple-400 mt-0.5"></i>
                            <span><strong>Self-Regulation & Pride:</strong> Organizing their personal belongings, expressing viewpoints confidently, and demonstrating empathy.</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-700/60 flex items-center justify-between text-xs text-emerald-300 font-semibold">
                    <span>Key Growth: Primary School Readiness & Self-Belief</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

        </div>
    </div>

    <!-- SECTION: 5 CORE PILLARS OF HOLISTIC EARLY CHILDHOOD EDUCATION -->
    <div id="programs" class="space-y-10 scroll-mt-24">
        <div class="text-center max-w-3xl mx-auto space-y-2">
            <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-amber-400/10 text-amber-300 border border-amber-400/20">
                Foundational Learning Pillars &bull; මූලික ඉගෙනුම් කුලුනු
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                How Our Pre-Schools Educate
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Rather than rote memorization, children flourish when learning is embedded in joyful, hands-on experiences across multiple developmental domains.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Pillar 1 -->
            <div class="p-6 rounded-3xl bg-slate-800/40 border border-slate-700/60 hover:border-amber-400/50 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-400/10 text-amber-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-gamepad"></i>
                </div>
                <h4 class="text-base font-bold text-white">Play-Based Learning</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Play is the serious work of childhood. Through guided purposeful play, children experiment with cause-and-effect, test ideas, and develop resilient problem-solving minds.
                </p>
            </div>

            <!-- Pillar 2 -->
            <div class="p-6 rounded-3xl bg-slate-800/40 border border-slate-700/60 hover:border-pink-400/50 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-pink-400/10 text-pink-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-music"></i>
                </div>
                <h4 class="text-base font-bold text-white">Music, Arts & Imagination</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Singing rhymes, dancing, rhythmic percussion, and messy art empower emotional expression while wiring neurological pathways for memory and spatial awareness.
                </p>
            </div>

            <!-- Pillar 3 -->
            <div class="p-6 rounded-3xl bg-slate-800/40 border border-slate-700/60 hover:border-cyan-400/50 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-cyan-400/10 text-cyan-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-language"></i>
                </div>
                <h4 class="text-base font-bold text-white">Bilingual Language Growth</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Rich immersion in both Sinhala mother-tongue culture and foundational English communication, cultivating natural bilingual fluency and confident conversational skills.
                </p>
            </div>

            <!-- Pillar 4 -->
            <div class="p-6 rounded-3xl bg-slate-800/40 border border-slate-700/60 hover:border-emerald-400/50 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-400/10 text-emerald-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-person-running"></i>
                </div>
                <h4 class="text-base font-bold text-white">Outdoor & Motor Skills</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Daily open-air play, balancing beams, ball games, and climbing promote cardiovascular health, strong bones, and vestibular balance, reducing screen fatigue.
                </p>
            </div>

            <!-- Pillar 5 -->
            <div class="p-6 rounded-3xl bg-slate-800/40 border border-slate-700/60 hover:border-purple-400/50 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-purple-400/10 text-purple-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <h4 class="text-base font-bold text-white">Emotional Safety & Nutrition</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    A nurturing, non-judgmental environment where every child feels loved and respected. Encouraging healthy eating habits, hand-washing hygiene, and peaceful rest.
                </p>
            </div>

            <!-- Pillar 6 -->
            <div class="p-6 rounded-3xl bg-slate-800/40 border border-slate-700/60 hover:border-indigo-400/50 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-400/10 text-indigo-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <h4 class="text-base font-bold text-white">Smart QR Campus Attendance</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Instant contactless scanning at central reception. Parents receive peace of mind, and schools maintain accurate digital attendance and safety records.
                </p>
            </div>
        </div>
    </div>

    <!-- SECTION: MEMBER PRE-SCHOOLS SHOWCASE -->
    <div id="preschools" class="space-y-8 scroll-mt-24">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                    Participating Campuses &bull; සහභාගී වන පෙර පාසල්
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-white mt-2">
                    Explore Our Member Pre-Schools
                </h2>
                <p class="text-xs text-slate-400">Choose a trusted pre-school to enroll your child or apply online.</p>
            </div>

            <a href="{{ route('public.apply') }}" 
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition-all flex items-center gap-2 self-start sm:self-auto">
                <span>View All & Apply</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($schools as $s)
                <div class="rounded-3xl bg-slate-800/60 border border-slate-700/80 p-6 flex flex-col justify-between hover:border-indigo-500/50 transition-all shadow-lg group">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="w-12 h-12 rounded-2xl gradient-brand text-white flex items-center justify-center text-xl font-bold shadow-md">
                                <i class="fa-solid fa-school"></i>
                            </div>
                            @if($s->is_admission_open)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Admissions Open</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold text-slate-400 bg-slate-800 border border-slate-700">
                                    Enrolling Soon
                                </span>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-white group-hover:text-indigo-400 transition-colors">
                            {{ $s->name ?: 'Member Pre-School' }}
                        </h3>
                        @if($s->motto)
                            <p class="text-xs italic text-amber-300 mt-1 line-clamp-1">"{{ $s->motto }}"</p>
                        @endif

                        <p class="text-xs text-slate-300 mt-3 line-clamp-3 leading-relaxed">
                            {{ $s->about_us ?: 'Dedicated to quality early childhood education, playgroup, nursery, kindergarten, and after-school daycare.' }}
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-700/60 space-y-3">
                        <div class="text-[11px] text-slate-400 space-y-1">
                            @if($s->contact_phone)
                                <p><i class="fa-solid fa-phone text-slate-500 w-4"></i> {{ $s->contact_phone }}</p>
                            @endif
                            @if($s->address)
                                <p class="truncate"><i class="fa-solid fa-location-dot text-slate-500 w-4"></i> {{ $s->address }}</p>
                            @endif
                        </div>

                        <a href="{{ route('public.apply', $s->slug ?: $s->registration_token) }}" 
                           class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
                            <i class="fa-solid fa-file-pen text-xs"></i>
                            <span>Apply to this Pre-School</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-10 text-center text-slate-400 bg-slate-800/40 rounded-3xl border border-slate-700">
                    <i class="fa-solid fa-school text-3xl mb-2 text-slate-500 block"></i>
                    <p>No member pre-schools listed yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- CALLOUT BANNER: FOR PRE-SCHOOL PRINCIPALS & STAFF -->
    <div class="rounded-3xl bg-gradient-to-r from-purple-900/60 via-indigo-900/60 to-slate-900/80 border border-indigo-500/30 p-8 sm:p-12 shadow-2xl relative overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
            <div class="md:col-span-8 space-y-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-400/20 text-indigo-300 border border-indigo-400/30">
                    Educator & Principal Access
                </span>
                <h3 class="text-2xl sm:text-3xl font-black text-white">
                    Are you a Pre-School Principal or Teacher?
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-xl">
                    Access your isolated staff portal to manage your classrooms (LKG, UKG), review online admissions, track teacher leave requests, collect tuition fees, and scan QR attendance at the central kiosk.
                </p>
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="{{ route('login') }}" 
                       class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs sm:text-sm font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Staff Portal Sign In</span>
                    </a>
                    <a href="{{ route('register') }}" 
                       class="px-5 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-semibold border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-school-flag"></i>
                        <span>Register as Principal</span>
                    </a>
                </div>
            </div>

            <div class="md:col-span-4 flex items-center justify-center">
                <div class="w-32 h-32 rounded-3xl bg-indigo-500/10 border-2 border-indigo-500/30 flex flex-col items-center justify-center text-center p-4 shadow-xl">
                    <i class="fa-solid fa-chalkboard-user text-4xl text-indigo-400 mb-2"></i>
                    <span class="text-xs font-black text-white">SaaS Cloud</span>
                    <span class="text-[10px] text-slate-400">Multi-School System</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
