import { GoogleGenAI } from "@google/genai";
import { ImageSize } from "../types";

// Helper to check if API key is selected via the specialized UI or available in env
export const checkApiKeyAvailability = async (): Promise<boolean> => {
  // If the API key is present in the environment variables, we consider it available.
  if (process.env.API_KEY) {
    return true;
  }

  // If running in Google IDX or AI Studio environment with the injector
  if (window.aistudio && window.aistudio.hasSelectedApiKey) {
    return await window.aistudio.hasSelectedApiKey();
  }

  // If the selector API isn't available, we shouldn't block the UI with a button that won't work.
  // We assume the user might have configured it differently or allow the API error to propagate naturally.
  return true;
};

// Helper to prompt user to select key
export const promptApiKeySelection = async (): Promise<void> => {
  if (window.aistudio && window.aistudio.openSelectKey) {
    await window.aistudio.openSelectKey();
  } else {
    console.warn("AI Studio key selector not available in this environment.");
  }
};

const extractImageFromResponse = (response: any): string => {
  // Iterate to find the image part
  if (response.candidates && response.candidates[0].content.parts) {
    for (const part of response.candidates[0].content.parts) {
      if (part.inlineData && part.inlineData.data) {
        return `data:image/png;base64,${part.inlineData.data}`;
      }
    }
  }
  throw new Error("No image data found in response");
};

export const generateImage = async (prompt: string, size: ImageSize): Promise<string> => {
  // Always create a fresh instance to ensure we capture the injected API key
  // process.env.API_KEY is injected by the environment after selection
  const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });
  
  // 1. Try Gemini 3 Pro (Best quality, specific sizes, requires Billing)
  try {
    const response = await ai.models.generateContent({
      model: 'gemini-3-pro-image-preview',
      contents: {
        parts: [{ text: prompt }]
      },
      config: {
        imageConfig: {
          aspectRatio: "1:1", 
          imageSize: size,
        }
      },
    });
    return extractImageFromResponse(response);

  } catch (error: any) {
    console.warn("Gemini 3 Pro generation failed (likely permission/billing), attempting fallback...", error);
    
    // 2. Fallback to Gemini 2.5 Flash Image (Good quality, standard size, often works on free tier)
    // Note: Flash image might not support explicit 'imageSize' param config in all SDK versions, 
    // so we omit it to ensure compatibility. It defaults to 1024x1024 (1K).
    try {
      const response = await ai.models.generateContent({
        model: 'gemini-2.5-flash-image',
        contents: {
          parts: [{ text: prompt }]
        },
        config: {
          imageConfig: {
            aspectRatio: "1:1",
          }
        },
      });
      return extractImageFromResponse(response);
    } catch (fallbackError: any) {
       console.error("Fallback generation failed:", fallbackError);
       
       // If it's a 403 Permission Denied, throw a user-friendly error
       if (error.message?.includes("403") || fallbackError.message?.includes("403")) {
          throw new Error("Permission Denied (403): The API Key provided does not have access to Image Generation. Please use a valid API Key with billing enabled.");
       }

       throw fallbackError;
    }
  }
};
