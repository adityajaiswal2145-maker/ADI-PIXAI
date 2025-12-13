import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';

const Register: React.FC = () => {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState('');
  
  const { register } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (password !== confirmPassword) {
      setError("Passwords do not match");
      return;
    }

    if (name && email && password) {
      register(name, email, password);
      navigate('/generator');
    }
  };

  return (
    <div className="min-h-screen pt-24 flex items-center justify-center bg-zinc-950 px-4">
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-900/20 via-zinc-950 to-zinc-950 pointer-events-none"></div>
      
      <div className="w-full max-w-md bg-zinc-900/50 backdrop-blur-xl border border-white/10 p-8 rounded-3xl shadow-2xl relative z-10">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-white mb-2 tracking-tight">Create Account</h1>
          <p className="text-zinc-400">Join ADI PIXAI & Get <span className="text-indigo-400 font-semibold">25 Free Credits</span></p>
        </div>

        {error && (
          <div className="mb-6 p-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg text-sm text-center">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-5">
          <div>
            <label className="block text-sm font-medium text-zinc-400 mb-1.5">Full Name</label>
            <input
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="w-full px-4 py-3 rounded-xl bg-zinc-950 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-zinc-600"
              placeholder="John Doe"
              required
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-zinc-400 mb-1.5">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-4 py-3 rounded-xl bg-zinc-950 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-zinc-600"
              placeholder="you@example.com"
              required
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-zinc-400 mb-1.5">Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full px-4 py-3 rounded-xl bg-zinc-950 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-zinc-600"
              placeholder="Min 8 chars"
              minLength={8}
              required
            />
          </div>
           <div>
            <label className="block text-sm font-medium text-zinc-400 mb-1.5">Confirm Password</label>
            <input
              type="password"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              className="w-full px-4 py-3 rounded-xl bg-zinc-950 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-zinc-600"
              placeholder="Confirm password"
              minLength={8}
              required
            />
          </div>
          <button
            type="submit"
            className="w-full py-4 mt-2 bg-white text-black font-bold rounded-xl hover:bg-zinc-200 transition-all transform hover:scale-[1.02] shadow-lg"
          >
            Create Account
          </button>
        </form>

        <div className="mt-8 text-center text-sm text-zinc-500">
          <p>Already have an account? <span className="text-white font-medium cursor-pointer hover:underline" onClick={() => navigate('/login')}>Sign In</span></p>
        </div>
      </div>
    </div>
  );
};

export default Register;
