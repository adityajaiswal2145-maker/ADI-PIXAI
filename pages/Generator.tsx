import React, { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { generateImage, checkApiKeyAvailability, promptApiKeySelection } from '../services/geminiService';
import { ImageSize, AIModel } from '../types';
import { Download, Loader2, Sparkles, KeyRound, ExternalLink, Cpu, Layers } from 'lucide-react';
import { Link } from 'react-router-dom';

const Generator: React.FC = () => {
  const { user, isAuthenticated, deductCredits } = useAuth();
  const [prompt, setPrompt] = useState('');
  const [size, setSize] = useState<ImageSize>('1K');
  const [selectedModel, setSelectedModel] = useState<AIModel>('gemini-3-pro');
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
    const interval = setInterval(checkKey, 2000);
    return () => clearInterval(interval);
  }, []);

  const handleSelectKey = async () => {
    await promptApiKeySelection();
    const isSet = await checkApiKeyAvailability();
    setApiKeySet(isSet);
  };

  const handleGenerate = async () => {
    if (!isAuthenticated || !user) {
      setError("Please log in to generate images.");
      return;
    }

    if (!apiKeySet) {
      setError("Please configuration is required.");
      return;
    }

    // Determine credit cost based on model
    const cost = selectedModel === 'gemini-3-pro' || selectedModel === 'dalle-3' ? 2 : 1;

    if (user.credits < cost) {
      setError(`Insufficient credits. This model requires ${cost} credits.`);
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
      const deductionSuccess = deductCredits(cost);
      if (!deductionSuccess) {
        throw new Error("Credit deduction failed.");
      }

      const imageUrl = await generateImage(prompt, size, selectedModel);
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
      link.download = `adi-pixai-${selectedModel}-${Date.now()}.png`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  };

  const models: {id: AIModel, name: string, description: string, cost: number}[] = [
    { id: 'gemini-3-pro', name: 'Gemini 3.0 Pro', description: 'Highest quality, best for complex prompts', cost: 2 },
    { id: 'gemini-2.5-flash', name: 'Gemini 2.5 Flash', description: 'Fast generation, good for concepts', cost: 1 },
    { id: 'dalle-3', name: 'Chat GPT (DALL-E 3)', description: 'Creative and artistic interpretations', cost: 2 },
    { id: 'stable-diffusion', name: 'Stable Diffusion', description: 'Balanced realism and art', cost: 1 },
  ];

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
          <h1 className="text-3xl md:text-4xl font-bold mb-3">AI Image Studio</h1>
          <p className="text-zinc-400">Create with Gemini 3.0, DALL-E 3, and more.</p>
        </div>

        {/* API Key Blocker / Selector */}
        {!apiKeySet && (
          <div className="mb-8 p-6 rounded-xl border border-amber-500/20 bg-amber-500/10 flex flex-col items-center text-center">
            <KeyRound className="w-10 h-10 text-amber-500 mb-3" />
            <h3 className="text-lg font-semibold text-amber-200 mb-2">Configuration Required</h3>
            <p className="text-zinc-300 mb-4 max-w-lg">
              System configuration check failed. Please ensure API keys are configured.
            </p>
          </div>
        )}

        {/* Input Area */}
        <div className={`p-1 rounded-2xl bg-gradient-to-br from-white/20 to-white/5 ${!apiKeySet ? 'opacity-50 pointer-events-none grayscale' : ''}`}>
          <div className="bg-zinc-900 rounded-xl p-6 md:p-8">
            <div className="flex flex-col gap-6">
              
              {/* Model Selection */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                 {models.map((m) => (
                    <button
                      key={m.id}
                      onClick={() => setSelectedModel(m.id)}
                      className={`flex flex-col text-left p-4 rounded-xl border transition-all ${
                        selectedModel === m.id 
                          ? 'border-indigo-500 bg-indigo-500/10 shadow-[0_0_15px_rgba(99,102,241,0.2)]' 
                          : 'border-white/5 bg-zinc-950/50 hover:bg-zinc-800 hover:border-white/10'
                      }`}
                    >
                       <div className="flex justify-between items-center w-full mb-1">
                          <span className={`font-semibold ${selectedModel === m.id ? 'text-white' : 'text-zinc-300'}`}>{m.name}</span>
                          <span className="text-xs font-mono px-2 py-0.5 rounded bg-white/5 text-zinc-400">{m.cost} Credits</span>
                       </div>
                       <span className="text-xs text-zinc-500">{m.description}</span>
                    </button>
                 ))}
              </div>

              <div className="relative">
                <textarea
                  value={prompt}
                  onChange={(e) => setPrompt(e.target.value)}
                  placeholder={`Describe what you want to see with ${models.find(m => m.id === selectedModel)?.name}...`}
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
                        disabled={selectedModel === 'gemini-2.5-flash'} // Flash is fixed size usually
                        className={`px-4 py-1.5 rounded-md text-sm font-medium transition-all ${
                          size === s 
                            ? 'bg-zinc-800 text-white shadow-sm' 
                            : 'text-zinc-500 hover:text-zinc-300'
                        } ${selectedModel === 'gemini-2.5-flash' ? 'opacity-50 cursor-not-allowed' : ''}`}
                      >
                        {s}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="flex items-center gap-4 w-full md:w-auto">
                   <div className="text-right hidden md:block">
                     <div className="text-xs text-zinc-500">Cost</div>
                     <div className="text-sm font-bold text-indigo-400">{models.find(m => m.id === selectedModel)?.cost} Credits</div>
                   </div>
                   <button
                    onClick={handleGenerate}
                    disabled={loading || !prompt.trim() || user.credits < (models.find(m => m.id === selectedModel)?.cost || 1)}
                    className="w-full md:w-auto flex items-center justify-center gap-2 px-8 py-3 bg-white text-black font-bold rounded-xl hover:bg-zinc-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95"
                  >
                    {loading ? (
                      <>
                        <Loader2 className="animate-spin" /> Generating...
                      </>
                    ) : (
                      <>
                        <Sparkles size={18} /> Generate
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
          <div className="mt-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg text-center flex items-center justify-center gap-2">
            <Layers size={16} /> {error}
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
               <p className="text-zinc-500 text-sm flex items-center justify-center gap-2">
                 <Cpu size={14}/> Generated with {models.find(m => m.id === selectedModel)?.name}
               </p>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default Generator;