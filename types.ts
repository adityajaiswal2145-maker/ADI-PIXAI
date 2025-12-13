export type Role = 'user' | 'admin';

export interface User {
  id: string;
  name: string;
  email: string;
  credits: number;
  joinedAt: Date;
  role: Role;
}

export type PaymentStatus = 'pending' | 'approved' | 'rejected';

export interface PaymentRequest {
  id: string;
  userId: string;
  userEmail: string; // Helper for display in admin
  planName: string; // e.g., "100 Credits"
  amount: number;
  credits: number;
  utrCode: string;
  date: string; // ISO String
  status: PaymentStatus;
  note?: string;
}

export interface GeneratedImage {
  id: string;
  url: string;
  prompt: string;
  createdAt: Date;
  size: '1K' | '2K' | '4K';
}

export type ImageSize = '1K' | '2K' | '4K';

export interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}

// Extend Window interface for the AI Studio key selection
declare global {
  interface AIStudio {
    hasSelectedApiKey: () => Promise<boolean>;
    openSelectKey: () => Promise<void>;
  }

  interface Window {
    aistudio?: AIStudio;
  }
}
