import React from 'react';
import { Link } from 'react-router-dom';
import { Sparkles, Shield, Zap, Download } from 'lucide-react';

const Landing: React.FC = () => {
  return (
    <div className="min-h-screen bg-zinc-950 text-white">
      {/* Hero Section */}
      <section className="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-indigo-900/20 via-zinc-950 to-zinc-950 pointer-events-none"></div>
        <div className="relative max-w-4xl mx-auto text-center">
          <h1 className="text-5xl md:text-7xl font-bold tracking-tight mb-6 bg-clip-text text-transparent bg-gradient-to-b from-white to-zinc-500">
            Create AI Images<br />in Seconds
          </h1>
          <p className="text-xl text-zinc-400 mb-10 max-w-2xl mx-auto">
            Generate stunning AI visuals from text, download instantly, and retain total privacy. Powered by ADI PIXAI Images.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link
              to="/generator"
              className="px-8 py-4 rounded-full bg-white text-black font-semibold text-lg hover:bg-zinc-200 transition-all shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.5)]"
            >
              Start Generating
            </Link>
            <Link
              to="/login"
              className="px-8 py-4 rounded-full border border-white/20 bg-white/5 text-white font-semibold text-lg hover:bg-white/10 transition-all backdrop-blur-sm"
            >
              Get 25 Free Credits
            </Link>
          </div>
        </div>
      </section>

      {/* Features Grid */}
      <section className="py-20 bg-zinc-900/50 border-t border-white/5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="p-8 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
              <div className="w-12 h-12 rounded-lg bg-indigo-500/20 flex items-center justify-center mb-4">
                <Zap className="text-indigo-400" size={24} />
              </div>
              <h3 className="text-xl font-semibold mb-2">Lightning Fast</h3>
              <p className="text-zinc-400">Powered by Gemini 3 Pro for rapid, high-resolution image synthesis.</p>
            </div>
            <div className="p-8 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
              <div className="w-12 h-12 rounded-lg bg-emerald-500/20 flex items-center justify-center mb-4">
                <Shield className="text-emerald-400" size={24} />
              </div>
              <h3 className="text-xl font-semibold mb-2">Privacy First</h3>
              <p className="text-zinc-400">No generated images are stored on our servers. Download and they're gone.</p>
            </div>
            <div className="p-8 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
              <div className="w-12 h-12 rounded-lg bg-pink-500/20 flex items-center justify-center mb-4">
                <Download className="text-pink-400" size={24} />
              </div>
              <h3 className="text-xl font-semibold mb-2">Instant Download</h3>
              <p className="text-zinc-400">Get your 1K, 2K, or 4K images instantly in high-quality formats.</p>
            </div>
          </div>
        </div>
      </section>

      {/* How it works */}
      <section className="py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl font-bold text-center mb-12">How It Works</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-center relative">
             <div className="relative z-10">
               <div className="w-16 h-16 mx-auto rounded-full bg-zinc-800 border border-white/10 flex items-center justify-center text-2xl font-bold mb-4">1</div>
               <h3 className="text-lg font-semibold">Sign Up</h3>
               <p className="text-zinc-400 mt-2">Create an account to get 25 free credits immediately.</p>
             </div>
             <div className="relative z-10">
               <div className="w-16 h-16 mx-auto rounded-full bg-zinc-800 border border-white/10 flex items-center justify-center text-2xl font-bold mb-4">2</div>
               <h3 className="text-lg font-semibold">Enter Prompt</h3>
               <p className="text-zinc-400 mt-2">Describe your vision. Choose between 1K, 2K, or 4K size.</p>
             </div>
             <div className="relative z-10">
               <div className="w-16 h-16 mx-auto rounded-full bg-zinc-800 border border-white/10 flex items-center justify-center text-2xl font-bold mb-4">3</div>
               <h3 className="text-lg font-semibold">Download</h3>
               <p className="text-zinc-400 mt-2">Save your creation instantly. No strings attached.</p>
             </div>
          </div>
        </div>
      </section>

       {/* Demo Gallery */}
       <section className="py-20 border-t border-white/5 bg-zinc-900/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl font-bold text-center mb-12">Made with ADI PIXAI</h2>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="aspect-square rounded-xl overflow-hidden bg-zinc-800 hover:scale-[1.02] transition-transform duration-300">
               <img src="https://picsum.photos/500/500?random=1" alt="Demo 1" className="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity" />
            </div>
            <div className="aspect-square rounded-xl overflow-hidden bg-zinc-800 hover:scale-[1.02] transition-transform duration-300">
               <img src="https://picsum.photos/500/500?random=2" alt="Demo 2" className="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity" />
            </div>
            <div className="aspect-square rounded-xl overflow-hidden bg-zinc-800 hover:scale-[1.02] transition-transform duration-300">
               <img src="https://picsum.photos/500/500?random=3" alt="Demo 3" className="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity" />
            </div>
            <div className="aspect-square rounded-xl overflow-hidden bg-zinc-800 hover:scale-[1.02] transition-transform duration-300">
               <img src="https://picsum.photos/500/500?random=4" alt="Demo 4" className="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity" />
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Landing;
