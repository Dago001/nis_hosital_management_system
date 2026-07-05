import { Component } from 'react';
import type { ErrorInfo, ReactNode } from 'react';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
  errorInfo: ErrorInfo | null;
}

export class ErrorBoundary extends Component<Props, State> {
  public state: State = {
    hasError: false,
    error: null,
    errorInfo: null
  };

  public static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error, errorInfo: null };
  }

  public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    this.setState({
      error,
      errorInfo
    });
    console.error('Uncaught error:', error, errorInfo);
  }

  public render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen bg-slate-900 flex items-center justify-center p-6 text-white">
          <div className="max-w-2xl w-full bg-red-950/50 border border-red-500/50 rounded-2xl p-8 shadow-2xl space-y-4">
            <h1 className="text-2xl font-bold text-red-400 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              Application Crashed
            </h1>
            <p className="text-slate-300">A fatal error occurred during rendering. Please copy this error and share it.</p>
            <div className="bg-black/50 p-4 rounded-xl overflow-x-auto text-xs font-mono border border-slate-800">
              <div className="text-red-300 font-bold mb-2">{this.state.error?.toString()}</div>
              <div className="text-slate-500 whitespace-pre-wrap">{this.state.errorInfo?.componentStack}</div>
            </div>
            <button 
              onClick={() => window.location.href = '/'} 
              className="mt-4 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg transition-colors"
            >
              Reload Application
            </button>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
