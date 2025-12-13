import React from 'react';

const Footer: React.FC = () => {
  return (
    <footer className="border-t border-white/10 bg-zinc-950 py-8 mt-auto">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <div className="text-zinc-500 text-sm">
          © 2025 ADI PIXAI. All rights reserved.
        </div>
        <div className="flex gap-6 text-sm text-zinc-400">
          <a href="#" className="hover:text-white transition-colors">Privacy Policy</a>
          <a href="#" className="hover:text-white transition-colors">Terms of Service</a>
          <a href="mailto:adityaarya1810@gmail.com" className="hover:text-white transition-colors">Contact</a>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
