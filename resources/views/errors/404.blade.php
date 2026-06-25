<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .clip-triangle { clip-path: polygon(50% 0%, 0% 100%, 100% 100%); }
        .beam-gradient {
            background: linear-gradient(to bottom, rgba(205, 220, 57, 0.7) 0%, rgba(205, 220, 57, 0.2) 100%);
        }
    </style>
</head>
<body class="bg-[#f4f6f9] min-h-screen flex flex-col font-sans">

    <!-- Main Content -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 w-full max-w-5xl mx-auto">
        
        <!-- Illustration Container -->
        <div class="relative w-full max-w-[650px] h-[380px] mb-12">
            
            <!-- Main Blue Screen -->
            <div class="absolute left-1/2 top-10 -translate-x-1/2 w-[75%] h-[280px] bg-[#1a73e8] rounded-md shadow-md border-r-[6px] border-b-[6px] border-[#1557b0] flex flex-col pt-12 pl-12 space-y-4 overflow-hidden z-0">
                <div class="w-[80%] h-2.5 bg-[#5ca0f9] rounded-full"></div>
                <div class="w-[60%] h-2.5 bg-[#5ca0f9] rounded-full"></div>
                <div class="w-[70%] h-2.5 bg-[#5ca0f9] rounded-full mt-4"></div>
                <div class="w-[30%] h-2.5 bg-[#5ca0f9] rounded-full"></div>
            </div>

            <!-- UFO Base -->
            <div class="absolute top-2 left-[12%] z-20 transform rotate-[-8deg]">
                <div class="w-[120px] h-[35px] bg-[#7c4dff] rounded-t-full relative border-[3px] border-[#5e35b1] mx-auto z-10">
                    <div class="absolute top-2 left-4 w-5 h-2.5 bg-white/30 rounded-full"></div>
                </div>
                <div class="w-[170px] h-[28px] bg-[#651fff] rounded-full -mt-[6px] border-[3px] border-[#4527a0] flex justify-center space-x-6 items-center shadow-lg relative z-20">
                    <div class="w-2.5 h-2.5 bg-[#cddc39] rounded-full"></div>
                    <div class="w-2.5 h-2.5 bg-[#cddc39] rounded-full"></div>
                    <div class="w-2.5 h-2.5 bg-[#cddc39] rounded-full"></div>
                </div>
            </div>

            <!-- Beam -->
            <div class="absolute top-[45px] left-[19%] z-10 w-[150px] h-[240px] beam-gradient" 
                 style="clip-path: polygon(30% 0, 70% 0, 100% 100%, 0 100%); transform: rotate(-15deg); transform-origin: top center;"></div>
                 
            <div class="absolute top-[260px] left-[26%] z-10 w-[110px] h-[30px] bg-black/20 rounded-full transform -rotate-[15deg] blur-[2px]"></div>

            <!-- Geometric Shapes -->
            <!-- Teal Square -->
            <div class="absolute bottom-2 left-[8%] z-30 w-[70px] h-[70px] bg-[#1de9b6] rounded-[6px]"></div>
            <!-- Orange Circle -->
            <div class="absolute bottom-12 left-[19%] z-40 w-[60px] h-[60px] bg-[#ffa726] rounded-full"></div>
            <!-- Pink Triangle -->
            <div class="absolute bottom-4 left-[31%] z-30 w-12 h-12 bg-[#ff4081] clip-triangle"></div>
            
            <!-- Teal pill -->
            <div class="absolute bottom-10 left-[41%] z-30 w-[60px] h-4 bg-[#00bfa5] rounded-full"></div>

            <!-- Chat Bubble -->
            <div class="absolute top-[100px] right-[8%] z-30 bg-[#1de9b6] rounded-[16px] rounded-br-none p-5 w-[190px]">
                <div class="w-[85%] h-2 bg-[#00897b] rounded-full mb-3"></div>
                <div class="w-[60%] h-2 bg-[#00897b] rounded-full"></div>
            </div>

            <!-- Target dot -->
            <div class="absolute top-[175px] right-[18%] z-20 w-6 h-6 border-4 border-[#ef6c00] rounded-full flex items-center justify-center">
                <div class="w-2 h-2 bg-[#ef6c00] rounded-full"></div>
            </div>
            
        </div>

        <!-- Typography -->
        <h1 class="text-[36px] font-bold text-black mb-2 text-center">Page Not Found!</h1>
        <p class="text-black text-[15px] font-bold mb-8 text-center">Sorry, the page your requested could not be found!.</p>

        <!-- Button -->
        <a href="{{ url('/attendance/clock') }}" 
           onclick="if(window.history.length > 1) { event.preventDefault(); window.history.back(); }"
           class="bg-[#1565c0] hover:bg-[#0d47a1] text-white font-bold py-3 px-8 rounded-md transition-colors text-[15px]">
            Go Home
        </a>

    </main>
</body>
</html>
