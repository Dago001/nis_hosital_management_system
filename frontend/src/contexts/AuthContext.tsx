import React, { createContext, useState, useEffect, useContext } from 'react';

interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string | null;
  mfa_enabled: boolean;
  status: string;
  roles: Array<{ name: string; display_name: string }>;
  staff?: {
    id: number;
    first_name: string;
    last_name: string;
    phone?: string;
    rank: string;
    service_number: string;
    department?: {
      id: number;
      name: string;
      code: string;
    };
  };
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  loading: boolean;
  login: (token: string, user: User) => void;
  logout: () => void;
  updateUser: (user: User) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    const savedToken = localStorage.getItem('nis_hms_token');
    const savedUser = localStorage.getItem('nis_hms_user');

    if (savedToken && savedUser) {
      setToken(savedToken);
      setUser(JSON.parse(savedUser));
    }
    setLoading(false);
  }, []);

  const login = (newToken: string, newUser: User) => {
    localStorage.setItem('nis_hms_token', newToken);
    localStorage.setItem('nis_hms_user', JSON.stringify(newUser));
    setToken(newToken);
    setUser(newUser);
  };

  const logout = () => {
    localStorage.removeItem('nis_hms_token');
    localStorage.removeItem('nis_hms_user');
    setToken(null);
    setUser(null);
  };

  const updateUser = (newUser: User) => {
    localStorage.setItem('nis_hms_user', JSON.stringify(newUser));
    setUser(newUser);
  };

  return (
    <AuthContext.Provider value={{ user, token, loading, login, logout, updateUser }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
