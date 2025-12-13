import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { Mail, Check, CreditCard, QrCode, ShieldCheck, History } from 'lucide-react';
import { motion } from 'framer-motion';

const Pricing: React.FC = () => {
  const { submitPaymentRequest, isAuthenticated, userPaymentHistory } = useAuth();
  const [selectedPlan, setSelectedPlan] = useState<{ credits: number, price: number, name: string } | null>(null);
  const [utr, setUtr] = useState('');
  const [note, setNote] = useState('');
  const [submitted, setSubmitted] = useState(false);

  const plans = [
    { credits: 50, price: 199, name: 'Basic' },
    { credits: 100, price: 299, name: 'Pro', popular: true },
    { credits: 200, price: 499, name: 'Ultra' },
  ];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (selectedPlan && utr) {
      submitPaymentRequest(selectedPlan.name, selectedPlan.price, selectedPlan.credits, utr, note);
      setSubmitted(true);
      setUtr('');
      setNote('');
      // Reset after a delay
      setTimeout(() => setSubmitted(false), 5000);
    }
  };

  return (
    <div className="min-h-screen pt-24 pb-12 px-4 bg-zinc-950 text-white">
      <div className="max-w-6xl mx-auto">
        
        {/* Header */}
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-white to-zinc-400">Upgrade Your Creativity</h2>
          <p className="text-zinc-400 text-lg">Secure manual payments via UPI. No hidden fees.</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
          
          {/* Plans Selection */}
          <div className="space-y-6">
             <h3 className="text-2xl font-semibold mb-4 flex items-center gap-2"><CreditCard className="text-indigo-400"/> Select a Plan</h3>
             <div className="grid gap-6">
                {plans.map((plan) => (
                  <motion.div 
                    key={plan.credits}
                    whileHover={{ scale: 1.02 }}
                    className={`relative p-6 rounded-2xl border cursor-pointer transition-all ${selectedPlan?.credits === plan.credits ? 'border-indigo-500 bg-indigo-500/10 shadow-[0_0_20px_rgba(99,102,241,0.2)]' : 'border-white/10 bg-zinc-900/50 hover:bg-zinc-900'}`}
                    onClick={() => setSelectedPlan(plan)}
                  >
                    {plan.popular && (
                      <div className="absolute -top-3 right-4 px-3 py-1 bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-xs font-bold rounded-full uppercase tracking-wide shadow-lg">
                        Most Popular
                      </div>
                    )}
                    <div className="flex justify-between items-center">
                      <div>
                        <h4 className="text-xl font-bold">{plan.credits} Credits</h4>
                        <p className="text-zinc-400 text-sm">₹{plan.price} INR</p>
                      </div>
                      <div className="text-2xl font-bold text-white">₹{plan.price}</div>
                    </div>
                  </motion.div>
                ))}
             </div>

             {/* Payment History Preview */}
             {isAuthenticated && userPaymentHistory.length > 0 && (
                <div className="mt-8 p-6 bg-zinc-900/50 rounded-2xl border border-white/10">
                  <h4 className="text-lg font-semibold mb-4 flex items-center gap-2 text-zinc-300"><History size={18}/> Your Recent Requests</h4>
                  <div className="space-y-3">
                    {userPaymentHistory.slice(0, 3).map((pay) => (
                      <div key={pay.id} className="flex justify-between text-sm py-2 border-b border-white/5 last:border-0">
                        <span className="text-zinc-400">{pay.planName} ({pay.amount} INR)</span>
                        <span className={`px-2 py-0.5 rounded-full text-xs font-medium capitalize ${
                          pay.status === 'approved' ? 'bg-green-500/20 text-green-400' :
                          pay.status === 'rejected' ? 'bg-red-500/20 text-red-400' :
                          'bg-amber-500/20 text-amber-400'
                        }`}>{pay.status}</span>
                      </div>
                    ))}
                  </div>
                </div>
             )}
          </div>

          {/* Payment Form & QR */}
          <div className="bg-white/5 border border-white/10 rounded-3xl p-8 backdrop-blur-xl">
            {selectedPlan ? (
               <div>
                  <div className="text-center mb-8">
                    <p className="text-sm text-zinc-400 mb-2">Scan to Pay ₹{selectedPlan.price}</p>
                    <div className="bg-white p-4 rounded-xl inline-block shadow-xl">
                      {/* Using the specific QR Code from requirements */}
                      <img 
                        src="https://image2url.com/images/1765622510915-0db3222b-126d-465e-8db1-d571528ea80a.jpg" 
                        alt="UPI QR Code" 
                        className="w-48 h-48 object-contain"
                      />
                    </div>
                    <div className="mt-4 flex flex-col items-center">
                      <span className="text-zinc-300 font-mono bg-zinc-900 px-3 py-1 rounded border border-white/10">7543008888@ybl</span>
                      <span className="text-xs text-zinc-500 mt-1">UPI ID</span>
                    </div>
                  </div>

                  <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                      <label className="block text-sm font-medium text-zinc-400 mb-1">Enter UTR / Transaction ID <span className="text-red-400">*</span></label>
                      <input
                        type="text"
                        value={utr}
                        onChange={(e) => setUtr(e.target.value)}
                        className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-zinc-600"
                        placeholder="e.g. 321456987456"
                        required
                        disabled={!isAuthenticated}
                      />
                      {!isAuthenticated && <p className="text-xs text-red-400 mt-1">Please login to submit payment.</p>}
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-zinc-400 mb-1">Note (Optional)</label>
                      <input
                        type="text"
                        value={note}
                        onChange={(e) => setNote(e.target.value)}
                        className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-zinc-600"
                        placeholder="Your name or specific request"
                        disabled={!isAuthenticated}
                      />
                    </div>
                    
                    {submitted ? (
                      <motion.div 
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="p-4 bg-green-500/20 border border-green-500/30 rounded-xl flex items-center gap-3 text-green-300"
                      >
                        <Check size={20} />
                        <div>
                          <p className="font-semibold">Payment Submitted!</p>
                          <p className="text-xs opacity-80">Admin will verify and update credits shortly.</p>
                        </div>
                      </motion.div>
                    ) : (
                      <button
                        type="submit"
                        disabled={!isAuthenticated || !utr}
                        className="w-full py-4 bg-white text-black font-bold rounded-xl hover:bg-zinc-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-[0_0_20px_rgba(255,255,255,0.2)] hover:shadow-[0_0_30px_rgba(255,255,255,0.4)]"
                      >
                        Submit Verification
                      </button>
                    )}
                  </form>
               </div>
            ) : (
              <div className="h-full flex flex-col items-center justify-center text-center p-8 opacity-50">
                <QrCode size={48} className="mb-4 text-zinc-500"/>
                <h3 className="text-xl font-semibold mb-2">No Plan Selected</h3>
                <p>Choose a credit package from the left to view payment details.</p>
              </div>
            )}
          </div>
        </div>

        {/* Enterprise / Footer info */}
        <div className="mt-16 text-center">
           <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-zinc-900 border border-white/10 text-zinc-400 text-sm">
              <ShieldCheck size={16} /> 
              Secure Manual Verification by ADI PIXAI Team
           </div>
           <p className="mt-4 text-zinc-500 text-sm">
             Issues with payment? Contact <a href="mailto:adityaarya1810@gmail.com" className="text-indigo-400 hover:underline">adityaarya1810@gmail.com</a>
           </p>
        </div>
      </div>
    </div>
  );
};

export default Pricing;
