import React, { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { generateImage, checkApiKeyAvailability, promptApiKeySelection } from '../services/geminiService';
import { ImageSize } from '../types';
import { Download, Loader2, Sparkles, KeyRound, ExternalLink } from 'lucide-react';
import { Link } from 'react-router-dom';

const Generator: React.FC = () => {
  const { user, isAuthenticated, deductCredits } = useAuth();
  const [prompt, setPrompt] = useState('');
  const [size, setSize] = useState<ImageSize>('1K');
  const [loading, setLoading] = useState(false);
  const [generatedImage, setGeneratedImage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [apiKeySet, setApiKeySet] = useState(false);

  // Check for API key availability on mount and periodically
  useEffect(() => {
    const checkKey = async () => {
      try {
        const isSet = await checkApiKeyAvailability();
        setApiKeySet(isSet);
      } catch (e) {
        console.error("Error checking API key status", e);
      }
    };
    
    checkKey();
    
    // Poll briefly to catch update if user just selected it
    const interval = setInterval(checkKey, 2000);
    return () => clearInterval(interval);
  }, []);

  const handleSelectKey = async () => {
    await promptApiKeySelection();
    // After returning from selection dialog, force a check
    const isSet = await checkApiKeyAvailability();
    setApiKeySet(isSet);
  };

  const handleGenerate = async () => {
    if (!isAuthenticated || !user) {
      setError("Please log in to generate images.");
      return;
    }

    if (!apiKeySet) {
      // Even though we show a blocker, double check here
      setError("Please select a paid API key to use the Pro model.");
      return;
    }

    if (user.credits < 2) {
      setError("Insufficient credits. Please purchase more.");
      return;
    }

    if (!prompt.trim()) {
      setError("Please enter a prompt.");
      return;
    }

    setLoading(true);
    setError(null);
    setGeneratedImage(null);

    try {
      // Deduct credits before generation (or after, depending on preference. PRD says "Auto deduction on image generation")
      const deductionSuccess = deductCredits(2);
      if (!deductionSuccess) {
        throw new Error("Credit deduction failed.");
      }

      const imageUrl = await generateImage(prompt, size);
      setGeneratedImage(imageUrl);
    } catch (err: any) {
      console.error(err);
      setError(err.message || "Failed to generate image. Try again.");
    } finally {
      setLoading(false);
    }
  };

  const handleDownload = () => {
    if (generatedImage) {
      const link = document.createElement('a');
      link.href = generatedImage;
      link.download = `adi-pixai-${Date.now()}.png`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  };

  if (!isAuthenticated) {
    return (
      <div className="min-h-screen pt-24 pb-12 px-4 flex items-center justify-center bg-zinc-950 text-white">
        <div className="text-center">
          <h2 className="text-2xl font-bold mb-4">Authentication Required</h2>
          <p className="text-zinc-400 mb-6">You need to be logged in to generate images.</p>
          <Link to="/login" className="px-6 py-3 bg-white text-black rounded-full font-medium hover:bg-zinc-200">
            Sign In / Register
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen pt-24 pb-12 px-4 sm:px-6 lg:px-8 bg-zinc-950 text-white">
      <div className="max-w-4xl mx-auto">
        
        {/* Header */}
        <div className="mb-10 text-center">
          <h1 className="text-3xl md:text-4xl font-bold mb-3">AI Image Generator</h1>
          <p className="text-zinc-400">Powered by Gemini 3 Pro Image Preview</p>
        </div>

        {/* API Key Blocker / Selector */}
        {!apiKeySet && (
          <div className="mb-8 p-6 rounded-xl border border-amber-500/20 bg-amber-500/10 flex flex-col items-center text-center">
            <KeyRound className="w-10 h-10 text-amber-500 mb-3" />
            <h3 className="text-lg font-semibold text-amber-200 mb-2">API Key Selection Required</h3>
            <p className="text-zinc-300 mb-4 max-w-lg">
              To use the high-quality <b>Gemini 3 Pro</b> model (supporting 2K/4K), you must select a paid API key associated with a Google Cloud Project.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 items-center">
               <button 
                onClick={handleSelectKey}
                className="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold rounded-lg transition-colors"
              >
                Select API Key
              </button>
              <a 
                href="https://ai.google.dev/gemini-api/docs/billing" 
                target="_blank" 
                rel="noreferrer"
                className="text-sm text-amber-400 hover:text-amber-300 underline flex items-center gap-1"
              >
                Read Billing Docs <ExternalLink size={12}/>
              </a>
            </div>
          </div>
        )}

        {/* Input Area */}
        <div className={`p-1 rounded-2xl bg-gradient-to-br from-white/20 to-white/5 ${!apiKeySet ? 'opacity-50 pointer-events-none grayscale' : ''}`}>
          <div className="bg-zinc-900 rounded-xl p-6 md:p-8">
            <div className="flex flex-col gap-6">
              
              <div className="relative">
                <textarea
                  value={prompt}
                  onChange={(e) => setPrompt(e.target.value)}
                  placeholder="Enter your imagination... (e.g., a futuristic cyberpunk city in rain, neon lights, 8k resolution)"
                  className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-4 text-lg text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 resize-none h-32"
                />
              </div>

              <div className="flex flex-col md:flex-row justify-between items-center gap-4">
                <div className="flex items-center gap-4">
                  <span className="text-sm text-zinc-400 font-medium">Resolution:</span>
                  <div className="flex p-1 bg-zinc-950 rounded-lg border border-zinc-800">
                    {(['1K', '2K', '4K'] as ImageSize[]).map((s) => (
                      <button
                        key={s}
                        onClick={() => setSize(s)}
                        className={`px-4 py-1.5 rounded-md text-sm font-medium transition-all ${
                          size === s 
                            ? 'bg-zinc-800 text-white shadow-sm' 
                            : 'text-zinc-500 hover:text-zinc-300'
                        }`}
                      >
                        {s}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="flex items-center gap-4 w-full md:w-auto">
                   <div className="text-right hidden md:block">
                     <div className="text-xs text-zinc-500">Cost</div>
                     <div className="text-sm font-bold text-indigo-400">2 Credits</div>
                   </div>
                   <button
                    onClick={handleGenerate}
                    disabled={loading || !prompt.trim() || user.credits < 2}
                    className="w-full md:w-auto flex items-center justify-center gap-2 px-8 py-3 bg-white text-black font-bold rounded-xl hover:bg-zinc-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95"
                  >
                    {loading ? (
                      <>
                        <Loader2 className="animate-spin" /> Generating...
                      </>
                    ) : (
                      <>
                        <Sparkles size={18} /> Generate Image
                      </>
                    )}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Error Message */}
        {error && (
          <div className="mt-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg text-center">
            {error}
          </div>
        )}

        {/* Result Area */}
        {generatedImage && (
          <div className="mt-12 animate-fade-in">
            <div className="relative group rounded-2xl overflow-hidden border border-white/10 shadow-2xl bg-zinc-900">
              <img 
                src={generatedImage} 
                alt={prompt} 
                className="w-full h-auto max-h-[700px] object-contain mx-auto"
              />
              
              {/* Overlay Actions */}
              <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
                <button
                  onClick={handleDownload}
                  className="px-6 py-3 bg-white text-black rounded-full font-bold flex items-center gap-2 hover:bg-zinc-200 transition-transform hover:scale-105"
                >
                  <Download size={20} /> Download {size}
                </button>
              </div>
            </div>
            <div className="mt-4 text-center">
               <p className="text-zinc-500 text-sm">Generated with Gemini 3 Pro ({size})</p>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default Generator;
