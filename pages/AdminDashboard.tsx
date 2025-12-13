import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';
import { Users, CreditCard, Activity, Check, X, Search, Edit2 } from 'lucide-react';
import { motion } from 'framer-motion';

const AdminDashboard: React.FC = () => {
  const { user, allUsers, paymentRequests, approvePayment, rejectPayment, updateUserCredits } = useAuth();
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState<'overview' | 'users' | 'payments'>('overview');
  const [searchTerm, setSearchTerm] = useState('');
  
  // Quick redirect if not admin
  React.useEffect(() => {
    if (!user || user.role !== 'admin') {
      navigate('/');
    }
  }, [user, navigate]);

  if (!user || user.role !== 'admin') return null;

  // Filter Logic
  const filteredUsers = allUsers.filter(u => 
    u.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
    u.email.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const pendingPayments = paymentRequests.filter(p => p.status === 'pending');
  const otherPayments = paymentRequests.filter(p => p.status !== 'pending');

  const stats = {
    totalUsers: allUsers.length,
    pendingRevenue: pendingPayments.reduce((acc, curr) => acc + curr.amount, 0),
    totalCreditsGiven: allUsers.reduce((acc, curr) => acc + curr.credits, 0),
  };

  return (
    <div className="min-h-screen pt-24 pb-12 px-4 bg-zinc-950 text-white">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
          <div>
            <h1 className="text-3xl font-bold">Admin Dashboard</h1>
            <p className="text-zinc-400">Manage users, credits, and payments.</p>
          </div>
          <div className="flex bg-zinc-900 p-1 rounded-lg border border-white/10">
            {(['overview', 'users', 'payments'] as const).map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`px-4 py-2 rounded-md text-sm font-medium transition-all ${activeTab === tab ? 'bg-indigo-600 text-white shadow' : 'text-zinc-400 hover:text-white'}`}
              >
                {tab.charAt(0).toUpperCase() + tab.slice(1)}
              </button>
            ))}
          </div>
        </div>

        {/* Overview Tab */}
        {activeTab === 'overview' && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
            <div className="p-6 bg-zinc-900/50 border border-white/10 rounded-2xl backdrop-blur-sm">
              <div className="flex items-center gap-4">
                <div className="p-3 bg-blue-500/20 rounded-xl text-blue-400"><Users size={24} /></div>
                <div>
                  <p className="text-zinc-400 text-sm">Total Users</p>
                  <p className="text-2xl font-bold">{stats.totalUsers}</p>
                </div>
              </div>
            </div>
            <div className="p-6 bg-zinc-900/50 border border-white/10 rounded-2xl backdrop-blur-sm">
              <div className="flex items-center gap-4">
                <div className="p-3 bg-amber-500/20 rounded-xl text-amber-400"><CreditCard size={24} /></div>
                <div>
                  <p className="text-zinc-400 text-sm">Pending Requests</p>
                  <p className="text-2xl font-bold">{pendingPayments.length} <span className="text-sm font-normal text-zinc-500">(₹{stats.pendingRevenue})</span></p>
                </div>
              </div>
            </div>
            <div className="p-6 bg-zinc-900/50 border border-white/10 rounded-2xl backdrop-blur-sm">
              <div className="flex items-center gap-4">
                <div className="p-3 bg-green-500/20 rounded-xl text-green-400"><Activity size={24} /></div>
                <div>
                  <p className="text-zinc-400 text-sm">Credits Distributed</p>
                  <p className="text-2xl font-bold">{stats.totalCreditsGiven}</p>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Users Tab */}
        {activeTab === 'users' && (
          <div className="animate-fade-in">
             <div className="mb-6 relative">
               <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" size={20}/>
               <input 
                 type="text" 
                 placeholder="Search users..." 
                 value={searchTerm}
                 onChange={(e) => setSearchTerm(e.target.value)}
                 className="w-full pl-10 pr-4 py-3 bg-zinc-900 border border-white/10 rounded-xl focus:outline-none focus:border-indigo-500 transition-colors"
               />
             </div>
             <div className="bg-zinc-900/50 border border-white/10 rounded-2xl overflow-hidden">
               <table className="w-full text-left border-collapse">
                 <thead>
                   <tr className="bg-zinc-900 border-b border-white/10 text-zinc-400 text-sm uppercase">
                     <th className="p-4 font-medium">User</th>
                     <th className="p-4 font-medium">Email</th>
                     <th className="p-4 font-medium">Role</th>
                     <th className="p-4 font-medium">Credits</th>
                     <th className="p-4 font-medium">Joined</th>
                     <th className="p-4 font-medium">Actions</th>
                   </tr>
                 </thead>
                 <tbody className="divide-y divide-white/5">
                   {filteredUsers.map(u => (
                     <tr key={u.id} className="hover:bg-white/5 transition-colors">
                       <td className="p-4 font-medium">{u.name}</td>
                       <td className="p-4 text-zinc-400">{u.email}</td>
                       <td className="p-4"><span className={`px-2 py-1 rounded text-xs ${u.role === 'admin' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-zinc-800 text-zinc-400'}`}>{u.role}</span></td>
                       <td className="p-4 text-indigo-300 font-mono">{u.credits}</td>
                       <td className="p-4 text-zinc-500 text-sm">{new Date(u.joinedAt).toLocaleDateString()}</td>
                       <td className="p-4">
                         <button 
                           onClick={() => {
                             const newAmount = prompt(`Update credits for ${u.name}:`, u.credits.toString());
                             if (newAmount !== null && !isNaN(parseInt(newAmount))) {
                               updateUserCredits(u.id, parseInt(newAmount));
                             }
                           }}
                           className="p-2 bg-zinc-800 hover:bg-zinc-700 rounded text-zinc-300"
                         >
                           <Edit2 size={16}/>
                         </button>
                       </td>
                     </tr>
                   ))}
                 </tbody>
               </table>
             </div>
          </div>
        )}

        {/* Payments Tab */}
        {activeTab === 'payments' && (
          <div className="space-y-8 animate-fade-in">
            {/* Pending Requests */}
            <div>
              <h3 className="text-xl font-bold mb-4 text-amber-400 flex items-center gap-2">Pending Requests <span className="bg-amber-500/20 text-amber-300 text-xs px-2 py-1 rounded-full">{pendingPayments.length}</span></h3>
              {pendingPayments.length === 0 ? (
                <div className="p-8 text-center text-zinc-500 bg-zinc-900/30 rounded-xl border border-white/5">No pending requests.</div>
              ) : (
                <div className="grid gap-4">
                  {pendingPayments.map(p => (
                    <div key={p.id} className="bg-zinc-900/80 border border-amber-500/20 p-6 rounded-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                      <div>
                        <div className="flex items-center gap-2 mb-1">
                           <span className="font-bold text-lg text-white">{p.planName}</span>
                           <span className="text-zinc-400">•</span>
                           <span className="text-green-400 font-mono">₹{p.amount}</span>
                        </div>
                        <p className="text-zinc-400 text-sm mb-2">User: <span className="text-white">{p.userEmail}</span></p>
                        <div className="flex flex-wrap gap-2">
                           <span className="bg-zinc-800 px-2 py-1 rounded text-xs text-zinc-300 font-mono">UTR: {p.utrCode}</span>
                           <span className="bg-zinc-800 px-2 py-1 rounded text-xs text-zinc-300">{new Date(p.date).toLocaleString()}</span>
                        </div>
                        {p.note && <p className="mt-2 text-sm text-zinc-500 italic">"{p.note}"</p>}
                      </div>
                      <div className="flex gap-3">
                        <button 
                          onClick={() => approvePayment(p.id)}
                          className="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg flex items-center gap-2 font-medium transition-colors"
                        >
                          <Check size={18} /> Approve
                        </button>
                        <button 
                          onClick={() => rejectPayment(p.id)}
                          className="px-4 py-2 bg-red-900/50 hover:bg-red-900 text-red-200 border border-red-800 rounded-lg flex items-center gap-2 font-medium transition-colors"
                        >
                          <X size={18} /> Reject
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* History */}
            <div>
              <h3 className="text-xl font-bold mb-4 text-zinc-400">Request History</h3>
              <div className="bg-zinc-900/30 border border-white/5 rounded-2xl overflow-hidden">
                 <table className="w-full text-left">
                   <thead className="bg-zinc-900/50 text-zinc-500 text-xs uppercase">
                     <tr>
                       <th className="p-4">Date</th>
                       <th className="p-4">User</th>
                       <th className="p-4">Plan</th>
                       <th className="p-4">Amount</th>
                       <th className="p-4">UTR</th>
                       <th className="p-4">Status</th>
                     </tr>
                   </thead>
                   <tbody className="divide-y divide-white/5 text-sm">
                     {otherPayments.map(p => (
                       <tr key={p.id}>
                         <td className="p-4 text-zinc-500">{new Date(p.date).toLocaleDateString()}</td>
                         <td className="p-4">{p.userEmail}</td>
                         <td className="p-4 text-zinc-300">{p.planName}</td>
                         <td className="p-4 text-zinc-300">₹{p.amount}</td>
                         <td className="p-4 font-mono text-xs text-zinc-500">{p.utrCode}</td>
                         <td className="p-4">
                           <span className={`px-2 py-1 rounded text-xs capitalize ${p.status === 'approved' ? 'text-green-400 bg-green-500/10' : 'text-red-400 bg-red-500/10'}`}>
                             {p.status}
                           </span>
                         </td>
                       </tr>
                     ))}
                     {otherPayments.length === 0 && (
                       <tr><td colSpan={6} className="p-8 text-center text-zinc-500">No history found.</td></tr>
                     )}
                   </tbody>
                 </table>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default AdminDashboard;
