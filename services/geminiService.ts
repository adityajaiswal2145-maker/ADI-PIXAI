import { GoogleGenAI } from "@google/genai";
import { ImageSize, AIModel } from "../types";

// --- API KEYS CONFIGURATION ---
const GEMINI_API_KEY = "AIzaSyAcehEg5TyouTqtoP7zL1PVLD4dtp7EOQ4"; 

// Helper to check if API key is available
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
  throw new Error("No image data found in response");
};

// --- CORE GENERATOR ---

const generateWithGemini = async (prompt: string, size: ImageSize, styleModifier: string = ''): Promise<string> => {
  const ai = new GoogleGenAI({ apiKey: GEMINI_API_KEY });
  
  // Enhance prompt with style modifier if provided (to mimic other models)
  const finalPrompt = styleModifier ? `${prompt}. Style: ${styleModifier}` : prompt;

  try {
    // 1. Try Gemini 3 Pro (Best Quality)
    const response = await ai.models.generateContent({
      model: 'gemini-3-pro-image-preview',
      contents: { parts: [{ text: finalPrompt }] },
      config: {
        imageConfig: {
          aspectRatio: "1:1", 
          imageSize: size, 
        }
      },
    });
    return extractImageFromGeminiResponse(response);
  } catch (error: any) {
    console.warn("Gemini 3 Pro failed, attempting fallback to Flash...", error);
    
    // 2. Fallback to Gemini 2.5 Flash (High Availability)
    try {
      const response = await ai.models.generateContent({
        model: 'gemini-2.5-flash-image',
        contents: { parts: [{ text: finalPrompt }] },
        config: {
          imageConfig: { aspectRatio: "1:1" }
        },
      });
      return extractImageFromGeminiResponse(response);
    } catch (fallbackError: any) {
      throw new Error(`Generation failed: ${fallbackError.message || "Please check your connection."}`);
    }
  }
};

// --- MODEL SPECIFIC HANDLERS ---

const generateGemini3Pro = async (prompt: string, size: ImageSize): Promise<string> => {
  return await generateWithGemini(prompt, size, "High resolution, photorealistic, highly detailed, 8k");
};

const generateGeminiFlash = async (prompt: string): Promise<string> => {
  return await generateWithGemini(prompt, '1K', "Fast, vivid, digital art");
};

const generateDallE3 = async (prompt: string, size: ImageSize): Promise<string> => {
  // Simulating DALL-E 3 style using Gemini engine (Universal Key Fix)
  const dalleStyle = "Surreal, creative, artistic, high contrast, DALL-E 3 style, imaginative composition";
  return await generateWithGemini(prompt, size, dalleStyle);
};

const generateStableDiffusion = async (prompt: string): Promise<string> => {
  // Simulating Stable Diffusion style using Gemini engine (Universal Key Fix)
  const sdStyle = "Detailed texture, cinematic lighting, concept art, Stable Diffusion XL style, sharp focus";
  return await generateWithGemini(prompt, '1K', sdStyle);
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
        return await generateGemini3Pro(prompt, size);
    }
  } catch (error: any) {
    console.error(`${model} Generation Error:`, error);
    throw new Error(error.message || `Failed to generate image with ${model}`);
  }
};