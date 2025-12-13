import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { Menu, X, Sun, Moon, Zap, ShieldCheck } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

const Navbar: React.FC = () => {
  const { user, isAuthenticated, logout } = useAuth();
  const { theme, toggleTheme } = useTheme();
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const location = useLocation();

  const isActive = (path: string) => location.pathname === path;

  return (
    <nav className="fixed top-0 w-full z-50 border-b border-white/10 backdrop-blur-xl bg-zinc-950/70 supports-[backdrop-filter]:bg-zinc-950/60">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <div className="flex-shrink-0">
            <Link to="/" className="flex items-center gap-2 group">
              <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-white to-zinc-500 flex items-center justify-center transition-transform group-hover:scale-105 shadow-lg shadow-white/10">
                <span className="font-bold text-black text-xl">A</span>
              </div>
              <span className="font-bold text-xl tracking-tight text-white group-hover:text-zinc-200 transition-colors">ADI PIXAI</span>
            </Link>
          </div>

          {/* Desktop Nav */}
          <div className="hidden md:block">
            <div className="ml-10 flex items-baseline space-x-1">
              <Link to="/" className={`px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 ${isActive('/') ? 'bg-white/10 text-white shadow-inner' : 'text-zinc-400 hover:text-white hover:bg-white/5'}`}>
                Home
              </Link>
              <Link to="/generator" className={`px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 ${isActive('/generator') ? 'bg-white/10 text-white shadow-inner' : 'text-zinc-400 hover:text-white hover:bg-white/5'}`}>
                Generator
              </Link>
              <Link to="/pricing" className={`px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 ${isActive('/pricing') ? 'bg-white/10 text-white shadow-inner' : 'text-zinc-400 hover:text-white hover:bg-white/5'}`}>
                Pricing
              </Link>
              
              {user?.role === 'admin' && (
                <Link to="/admin" className={`px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 flex items-center gap-1.5 ${isActive('/admin') ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10'}`}>
                  <ShieldCheck size={14} /> Admin
                </Link>
              )}
            </div>
          </div>

          {/* Right Side Actions */}
          <div className="hidden md:flex items-center gap-4">
            <button
              onClick={toggleTheme}
              className="p-2 rounded-full text-zinc-400 hover:text-white hover:bg-white/10 transition-colors"
            >
              {theme === 'dark' ? <Sun size={20} /> : <Moon size={20} />}
            </button>

            {isAuthenticated && user ? (
              <div className="flex items-center gap-4">
                <div className="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 shadow-[0_0_10px_rgba(99,102,241,0.1)]">
                  <Zap size={14} className="text-indigo-400" fill="currentColor" />
                  <span className="text-sm font-semibold text-indigo-300">{user.credits} Credits</span>
                </div>
                <div className="text-sm font-medium text-zinc-300">{user.name}</div>
                <button
                  onClick={logout}
                  className="px-3 py-2 text-sm text-red-400 hover:text-red-300 transition-colors"
                >
                  Logout
                </button>
              </div>
            ) : (
              <Link
                to="/login"
                className="px-5 py-2 text-sm font-semibold text-black bg-white rounded-full hover:bg-zinc-200 transition-all hover:scale-105 shadow-[0_0_15px_rgba(255,255,255,0.2)]"
              >
                Sign In
              </Link>
            )}
          </div>

          {/* Mobile menu button */}
          <div className="-mr-2 flex md:hidden">
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="inline-flex items-center justify-center p-2 rounded-md text-zinc-400 hover:text-white hover:bg-white/10 focus:outline-none"
            >
              {isMenuOpen ? <X size={24} /> : <Menu size={24} />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      <AnimatePresence>
        {isMenuOpen && (
          <motion.div 
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="md:hidden bg-zinc-950 border-b border-white/10 overflow-hidden"
          >
            <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3">
              <Link to="/" onClick={() => setIsMenuOpen(false)} className="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-white/10">Home</Link>
              <Link to="/generator" onClick={() => setIsMenuOpen(false)} className="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-white/10">Generator</Link>
              <Link to="/pricing" onClick={() => setIsMenuOpen(false)} className="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-white/10">Pricing</Link>
              
              {user?.role === 'admin' && (
                <Link to="/admin" onClick={() => setIsMenuOpen(false)} className="block px-3 py-2 rounded-md text-base font-medium text-indigo-400 hover:bg-indigo-500/10">Admin Dashboard</Link>
              )}

              {isAuthenticated ? (
                 <>
                   <div className="px-3 py-2 text-indigo-400 font-medium flex items-center gap-2">
                      <Zap size={16} fill="currentColor"/> {user?.credits} Credits
                   </div>
                   <button onClick={() => { logout(); setIsMenuOpen(false); }} className="block w-full text-left px-3 py-2 text-base font-medium text-red-400 hover:bg-white/10">Logout</button>
                 </>
              ) : (
                <Link to="/login" onClick={() => setIsMenuOpen(false)} className="block px-3 py-2 rounded-md text-base font-medium text-black bg-white hover:bg-zinc-200 mt-4 text-center">Sign In</Link>
              )}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </nav>
  );
};

export default Navbar;
