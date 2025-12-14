import { GoogleGenAI } from "@google/genai";
import { ImageSize, AIModel } from "../types";

// --- API KEYS CONFIGURATION ---
const GEMINI_API_KEY = "AIzaSyAcehEg5TyouTqtoP7zL1PVLD4dtp7EOQ4"; 
// Note: For DALL-E and Stable Diffusion, in a real production app these should be 
// environment variables or user-provided. 
const OPENAI_API_KEY = ""; // Add your OpenAI Key here if desired
const STABILITY_API_KEY = ""; // Add your Stability AI Key here if desired

// Helper to check if API key is available (Legacy support for UI)
export const checkApiKeyAvailability = async (): Promise<boolean> => {
  return true;
};

export const promptApiKeySelection = async (): Promise<void> => {
  console.log("Using configured API keys.");
};

const extractImageFromGeminiResponse = (response: any): string => {
  if (response.candidates && response.candidates[0].content.parts) {
    for (const part of response.candidates[0].content.parts) {
      if (part.inlineData && part.inlineData.data) {
        return `data:image/png;base64,${part.inlineData.data}`;
      }
    }
  }
  throw new Error("No image data found in Gemini response");
};

// --- MODEL SPECIFIC GENERATORS ---

const generateGemini3Pro = async (prompt: string, size: ImageSize): Promise<string> => {
  const ai = new GoogleGenAI({ apiKey: GEMINI_API_KEY });
  const response = await ai.models.generateContent({
    model: 'gemini-3-pro-image-preview',
    contents: { parts: [{ text: prompt }] },
    config: {
      imageConfig: {
        aspectRatio: "1:1", 
        imageSize: size, // Supports 1K, 2K, 4K
      }
    },
  });
  return extractImageFromGeminiResponse(response);
};

const generateGeminiFlash = async (prompt: string): Promise<string> => {
  const ai = new GoogleGenAI({ apiKey: GEMINI_API_KEY });
  const response = await ai.models.generateContent({
    model: 'gemini-2.5-flash-image',
    contents: { parts: [{ text: prompt }] },
    config: {
      imageConfig: { aspectRatio: "1:1" }
    },
  });
  return extractImageFromGeminiResponse(response);
};

const generateDallE3 = async (prompt: string, size: ImageSize): Promise<string> => {
  if (!OPENAI_API_KEY) throw new Error("OpenAI API Key is missing in configuration.");
  
  // Map size to DALL-E supported sizes
  const dalleSize = size === '1K' ? "1024x1024" : "1024x1024"; // DALL-E 3 standard

  const response = await fetch("https://api.openai.com/v1/images/generations", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Authorization": `Bearer ${OPENAI_API_KEY}`
    },
    body: JSON.stringify({
      model: "dall-e-3",
      prompt: prompt,
      n: 1,
      size: dalleSize,
      response_format: "b64_json"
    })
  });

  if (!response.ok) {
    const err = await response.json();
    throw new Error(err.error?.message || "DALL-E generation failed");
  }

  const data = await response.json();
  return `data:image/png;base64,${data.data[0].b64_json}`;
};

const generateStableDiffusion = async (prompt: string): Promise<string> => {
  // Using a common free/freemium endpoint or Stability AI API
  // Using Stability AI v1 text-to-image as example
  if (!STABILITY_API_KEY) throw new Error("Stability AI Key is missing in configuration.");

  const response = await fetch("https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      Authorization: `Bearer ${STABILITY_API_KEY}`,
    },
    body: JSON.stringify({
      text_prompts: [{ text: prompt }],
      cfg_scale: 7,
      height: 1024,
      width: 1024,
      samples: 1,
      steps: 30,
    }),
  });

  if (!response.ok) throw new Error(`Stability AI Error: ${response.statusText}`);

  const data = await response.json();
  return `data:image/png;base64,${data.artifacts[0].base64}`;
};

// --- MAIN CONTROLLER ---

export const generateImage = async (prompt: string, size: ImageSize, model: AIModel = 'gemini-3-pro'): Promise<string> => {
  try {
    switch (model) {
      case 'gemini-3-pro':
        return await generateGemini3Pro(prompt, size);
      
      case 'gemini-2.5-flash':
        return await generateGeminiFlash(prompt);
      
      case 'dalle-3':
        return await generateDallE3(prompt, size);
      
      case 'stable-diffusion':
        return await generateStableDiffusion(prompt);
      
      default:
        // Default fallback to Gemini 3 Pro
        return await generateGemini3Pro(prompt, size);
    }
  } catch (error: any) {
    console.error(`${model} Generation Error:`, error);
    
    // Auto-fallback logic specifically for Gemini models
    if (model === 'gemini-3-pro') {
      console.log("Falling back to Gemini 2.5 Flash...");
      try {
        return await generateGeminiFlash(prompt);
      } catch (fbError) {
        throw new Error("All Gemini models failed. Please try again later.");
      }
    }

    throw new Error(error.message || `Failed to generate image with ${model}`);
  }
};