import { ImageSize } from "../types";

// Helper to check if API key is selected via the specialized UI or available in env
export const checkApiKeyAvailability = async (): Promise<boolean> => {
  // We are using a managed RapidAPI key now to ensure service availability
  // so we always return true to unblock the UI.
  return true;
};

// Helper to prompt user to select key
export const promptApiKeySelection = async (): Promise<void> => {
  // No longer needed as we use a managed key
  console.log("Using managed API key.");
};

export const generateImage = async (prompt: string, size: ImageSize): Promise<string> => {
  // Map sizes to pixel dimensions supported by the API
  let width = 1024;
  let height = 1024;

  if (size === '2K') {
    width = 2048;
    height = 2048;
  } else if (size === '4K') {
    // Some APIs cap at 2048 or 3072, but we'll try to request higher if selected
    width = 2048; 
    height = 2048;
  }

  const seed = Math.floor(Math.random() * 10000);
  
  // The RapidAPI endpoint expects the prompt as part of the URL path
  const encodedPrompt = encodeURIComponent(prompt);
  
  // Construct the URL based on the curl example provided
  const url = `https://gemini-3-pro-image-nano-banana-pro-multi-image-editor.p.rapidapi.com/image/${encodedPrompt}?width=${width}&height=${height}&seed=${seed}&quality=high`;

  const options = {
    method: 'GET',
    headers: {
      'x-rapidapi-key': '67a2bbb8d1mshb3fbdf9c227ad18p1cd67fjsnaedaf2885361',
      'x-rapidapi-host': 'gemini-3-pro-image-nano-banana-pro-multi-image-editor.p.rapidapi.com'
    }
  };

  try {
    const response = await fetch(url, options);

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Generation failed (${response.status}): ${errorText}`);
    }

    // Check Content-Type to determine how to handle the response
    const contentType = response.headers.get('content-type');

    if (contentType && contentType.includes('application/json')) {
      const data = await response.json();
      // Handle JSON response (some APIs return a URL in JSON)
      if (data.url) return data.url;
      if (data.image) return data.image; // Could be a URL or Base64 string
      
      console.warn("Unexpected JSON response:", data);
      throw new Error("Received JSON response but could not extract image.");
    } else {
      // Handle Binary Image Response (Blob)
      const blob = await response.blob();
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result as string);
        reader.onerror = reject;
        reader.readAsDataURL(blob);
      });
    }

  } catch (error: any) {
    console.error("RapidAPI Generation Error:", error);
    throw new Error(error.message || "Failed to generate image. Please try again.");
  }
};
