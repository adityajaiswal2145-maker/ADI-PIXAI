import React, { createContext, useContext, useState, useEffect } from 'react';
import { User, PaymentRequest, PaymentStatus } from '../types';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  allUsers: User[]; // Admin only
  paymentRequests: PaymentRequest[]; // Admin only
  userPaymentHistory: PaymentRequest[]; // Current user's history
  login: (email: string, password?: string) => boolean; 
  register: (name: string, email: string, password?: string) => void;
  logout: () => void;
  deductCredits: (amount: number) => boolean;
  
  // Payment Functions
  submitPaymentRequest: (planName: string, amount: number, credits: number, utrCode: string, note?: string) => void;
  
  // Admin Functions
  approvePayment: (paymentId: string) => void;
  rejectPayment: (paymentId: string) => void;
  updateUserCredits: (userId: string, newAmount: number) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

// Initial Mock Data
const INITIAL_ADMIN: User = {
  id: 'admin-001',
  name: 'Aditya Admin',
  email: 'adityaarya0018@gmail.com',
  credits: 99999,
  joinedAt: new Date(),
  role: 'admin'
};

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [allUsers, setAllUsers] = useState<User[]>([]);
  const [paymentRequests, setPaymentRequests] = useState<PaymentRequest[]>([]);

  // Load persisted data
  useEffect(() => {
    const storedUser = localStorage.getItem('adipixai_user');
    const storedAllUsers = localStorage.getItem('adipixai_db_users');
    const storedPayments = localStorage.getItem('adipixai_db_payments');

    if (storedUser) setUser(JSON.parse(storedUser));
    if (storedAllUsers) setAllUsers(JSON.parse(storedAllUsers));
    if (storedPayments) setPaymentRequests(JSON.parse(storedPayments));
    
    // Ensure admin exists in mock DB
    if (!storedAllUsers) {
      setAllUsers([INITIAL_ADMIN]);
    }
  }, []);

  // Sync DB to local storage whenever it changes
  useEffect(() => {
    localStorage.setItem('adipixai_db_users', JSON.stringify(allUsers));
  }, [allUsers]);

  useEffect(() => {
    localStorage.setItem('adipixai_db_payments', JSON.stringify(paymentRequests));
  }, [paymentRequests]);


  const login = (email: string, password?: string): boolean => {
    // Admin Check
    if (email === 'adityaarya0018@gmail.com' && password === 'arya0018@') {
      setUser(INITIAL_ADMIN);
      localStorage.setItem('adipixai_user', JSON.stringify(INITIAL_ADMIN));
      return true;
    }

    // Normal User Check (Mock)
    const existingUser = allUsers.find(u => u.email === email);
    if (existingUser) {
      setUser(existingUser);
      localStorage.setItem('adipixai_user', JSON.stringify(existingUser));
      return true;
    }
    
    // For demo purposes, if user not found but not admin, return false
    // In real app we verify password hash
    return false;
  };

  const register = (name: string, email: string, password?: string) => {
    // Check if user already exists
    if (allUsers.find(u => u.email === email)) {
      alert("User already exists!");
      return;
    }

    const newUser: User = {
      id: Date.now().toString(),
      name,
      email,
      credits: 25, // 25 Free credits
      joinedAt: new Date(),
      role: 'user'
    };

    setAllUsers(prev => [...prev, newUser]);
    setUser(newUser);
    localStorage.setItem('adipixai_user', JSON.stringify(newUser));
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem('adipixai_user');
  };

  const deductCredits = (amount: number): boolean => {
    if (!user) return false;
    
    // Admins have infinite credits effectively, but let's track it
    if (user.role === 'admin') return true;

    if (user.credits >= amount) {
      const updatedUser = { ...user, credits: user.credits - amount };
      
      // Update local session
      setUser(updatedUser);
      localStorage.setItem('adipixai_user', JSON.stringify(updatedUser));

      // Update mock DB
      setAllUsers(prev => prev.map(u => u.id === user.id ? updatedUser : u));
      return true;
    }
    return false;
  };

  // --- Payment Logic ---

  const submitPaymentRequest = (planName: string, amount: number, credits: number, utrCode: string, note?: string) => {
    if (!user) return;

    const newRequest: PaymentRequest = {
      id: `pay_${Date.now()}`,
      userId: user.id,
      userEmail: user.email,
      planName,
      amount,
      credits,
      utrCode,
      date: new Date().toISOString(),
      status: 'pending',
      note
    };

    setPaymentRequests(prev => [newRequest, ...prev]);
  };

  const approvePayment = (paymentId: string) => {
    const request = paymentRequests.find(p => p.id === paymentId);
    if (!request || request.status !== 'pending') return;

    // Update Request Status
    const updatedRequests = paymentRequests.map(p => 
      p.id === paymentId ? { ...p, status: 'approved' as PaymentStatus } : p
    );
    setPaymentRequests(updatedRequests);

    // Add Credits to User
    const targetUser = allUsers.find(u => u.id === request.userId);
    if (targetUser) {
      const updatedUser = { ...targetUser, credits: targetUser.credits + request.credits };
      setAllUsers(prev => prev.map(u => u.id === request.userId ? updatedUser : u));
      
      // If the currently logged in user is the one being approved, update session
      if (user && user.id === request.userId) {
        setUser(updatedUser);
        localStorage.setItem('adipixai_user', JSON.stringify(updatedUser));
      }
    }
  };

  const rejectPayment = (paymentId: string) => {
    setPaymentRequests(prev => prev.map(p => 
      p.id === paymentId ? { ...p, status: 'rejected' as PaymentStatus } : p
    ));
  };

  // --- Admin Logic ---

  const updateUserCredits = (userId: string, newAmount: number) => {
    const updatedUser = allUsers.find(u => u.id === userId);
    if (updatedUser) {
      const modified = { ...updatedUser, credits: newAmount };
      setAllUsers(prev => prev.map(u => u.id === userId ? modified : u));
      
      if (user && user.id === userId) {
        setUser(modified);
        localStorage.setItem('adipixai_user', JSON.stringify(modified));
      }
    }
  };

  const userPaymentHistory = paymentRequests.filter(p => user ? p.userId === user.id : false);

  return (
    <AuthContext.Provider value={{ 
      user, 
      isAuthenticated: !!user, 
      allUsers,
      paymentRequests,
      userPaymentHistory,
      login, 
      register, 
      logout,
      deductCredits,
      submitPaymentRequest,
      approvePayment,
      rejectPayment,
      updateUserCredits
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within an AuthProvider");
  return context;
};
