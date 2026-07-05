import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';
import logo from '../assets/nis_logo.jpg';
import loginBg from '../assets/login_bg.jpg';
import { ShieldCheck, Mail, Lock, AlertCircle, KeyRound } from 'lucide-react';

export const Login: React.FC = () => {
  const { login } = useAuth();
  const navigate = useNavigate();
  
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  
  const [mfaRequired, setMfaRequired] = useState(false);
  const [mfaCode, setMfaCode] = useState('');
  
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      if (mfaRequired) {
        // MFA verification pathway
        const res = await api.post('/verify-mfa', { email, code: mfaCode });
        login(res.data.access_token, res.data.user);
        navigate('/');
      } else {
        // Initial login check
        const res = await api.post('/login', { email, password });
        if (res.data.mfa_required) {
          setMfaRequired(true);
        } else {
          login(res.data.access_token, res.data.user);
          navigate('/');
        }
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Login failed. Please check your credentials.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div 
      className="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden"
      style={{
        backgroundImage: `url(${loginBg})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat'
      }}
    >
      {/* Background overlay for styling and legibility */}
      <div className="absolute inset-0 bg-slate-900/60 z-0"></div>


      <div className="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <div className="flex justify-center">
          <img 
            src={logo} 
            alt="NIS Logo" 
            className="h-24 w-24 object-contain rounded-xl bg-white border-2 border-primary/20 p-1 shadow-lg shadow-primary/10" 
          />
        </div>
        <h2 className="mt-6 text-center text-3xl font-extrabold text-white tracking-tight font-sans">
          NIS MEDICAL SERVICES
        </h2>
        <p className="mt-2 text-center text-sm text-slate-800 dark:text-slate-200">
          Nigeria Immigration Service Hospital Management System (HMS)
        </p>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4">
        <div className="bg-slate-900/60 backdrop-blur-md border border-slate-800/80 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
          
          {error && (
            <div className="mb-4 bg-red-500/10 border border-red-500/20 rounded-xl p-3 flex items-start gap-3">
              <AlertCircle className="text-red-400 shrink-0 mt-0.5" size={18} />
              <span className="text-xs text-red-400 font-medium">{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            {!mfaRequired ? (
              <>
                <div>
                  <label className="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                    Staff Email Address
                  </label>
                  <div className="mt-1.5 relative">
                    <input
                      type="email"
                      required
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="admin@nishms.gov.ng"
                      className="w-full bg-slate-900/60 border border-slate-800/80 rounded-xl py-3 pl-10 pr-4 text-white text-sm placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                    />
                    <Mail size={16} className="absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-600" />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                    Password
                  </label>
                  <div className="mt-1.5 relative">
                    <input
                      type="password"
                      required
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="••••••••••••"
                      className="w-full bg-slate-900/60 border border-slate-800/80 rounded-xl py-3 pl-10 pr-4 text-white text-sm placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                    />
                    <Lock size={16} className="absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-600" />
                  </div>
                </div>
              </>
            ) : (
              <div>
                <div className="text-center mb-6">
                  <div className="inline-flex p-3 rounded-full bg-primary/10 border border-primary/20 text-primary mb-3">
                    <KeyRound size={24} />
                  </div>
                  <h3 className="text-md font-bold text-white">Enter MFA Code</h3>
                  <p className="text-xs text-slate-800 dark:text-slate-200 mt-1">
                    We sent a 6-digit confirmation code to your email. Check your application logs if in development.
                  </p>
                </div>
                
                <label className="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-center">
                  Verification OTP Code
                </label>
                <div className="mt-1.5 relative">
                  <input
                    type="text"
                    required
                    maxLength={6}
                    value={mfaCode}
                    onChange={(e) => setMfaCode(e.target.value)}
                    placeholder="123456"
                    className="w-full text-center bg-slate-900/60 border border-slate-800/80 rounded-xl py-3 text-white text-lg tracking-widest font-bold placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                  />
                </div>
              </div>
            )}

            <div>
              <button
                type="submit"
                disabled={loading}
                className="w-full bg-primary hover:bg-primary-dark text-white rounded-xl py-3 text-sm font-semibold shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all flex justify-center items-center gap-2 cursor-pointer disabled:opacity-50"
              >
                {loading ? 'Authenticating...' : mfaRequired ? 'Verify Passcode' : 'Access System Portal'}
              </button>
            </div>
          </form>

          {!mfaRequired && (
            <div className="mt-6 text-center">
              <span className="text-[10px] text-slate-600 font-semibold tracking-wide flex items-center justify-center gap-1.5 uppercase">
                <ShieldCheck size={12} className="text-primary" /> SECURED GOVERNMENT INFORMATION PORTAL
              </span>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};



