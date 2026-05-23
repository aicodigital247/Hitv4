/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState, useEffect } from "react";
import { 
  Layers, Cpu, Wallet, Lock, Settings, Search, Plus, Trash, Play, 
  CheckCircle, TrendingUp, X, ChevronRight, Copy, FileText, Database, 
  Smartphone, Info, LogOut, Globe, Activity, User, ShoppingCart, 
  AlertTriangle, Flame, Award, Heart, MessageSquare, ListFilter, Share2
} from "lucide-react";

// Definitions of Generated File Contents for the visual Sourced Code Hub
const GENERATED_FILES: Record<string, { path: string, language: string, code: string }> = {
  "Database Schema (betelite.sql)": {
    path: "/database/betelite.sql",
    language: "sql",
    code: `-- BETELITE MySQL Database Schema
-- Optimized for MySQL 5.7+ / PHP 8+ and shared hosting environments

CREATE TABLE IF NOT EXISTS \`users\` (
  \`id\` INT AUTO_INCREMENT PRIMARY KEY,
  \`username\` VARCHAR(50) NOT NULL UNIQUE,
  \`email\` VARCHAR(100) NOT NULL UNIQUE,
  \`password\` VARCHAR(255) NOT NULL,
  \`role\` ENUM('admin', 'predictor', 'user') NOT NULL DEFAULT 'user',
  \`status\` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
  \`referral_code\` VARCHAR(20) NOT NULL UNIQUE,
  \`referred_by\` INT DEFAULT NULL,
  \`vip_until\` DATETIME DEFAULT NULL,
  \`telegram_id\` VARCHAR(100) DEFAULT NULL,
  \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS \`wallets\` (
  \`user_id\` INT PRIMARY KEY,
  \`balance\` DECIMAL(15, 2) NOT NULL DEFAULT '0.00',
  \`pending_withdrawals\` DECIMAL(15, 2) NOT NULL DEFAULT '0.00',
  FOREIGN KEY (\`user_id\`) REFERENCES \`users\`(\`id\`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS \`predictions\` (
  \`id\` INT AUTO_INCREMENT PRIMARY KEY,
  \`predictor_id\` INT NOT NULL,
  \`title\` VARCHAR(255) NOT NULL,
  \`description\` TEXT,
  \`price\` DECIMAL(10, 2) NOT NULL DEFAULT '0.00',
  \`sport_type\` VARCHAR(50) NOT NULL DEFAULT 'Football',
  \`total_odds\` DECIMAL(8, 2) NOT NULL DEFAULT '1.00',
  \`confidence\` INT NOT NULL DEFAULT 85,
  \`status\` ENUM('pending', 'won', 'lost', 'refunded') NOT NULL DEFAULT 'pending',
  \`is_vip\` TINYINT(1) NOT NULL DEFAULT 0,
  \`is_hot\` TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (\`predictor_id\`) REFERENCES \`users\`(\`id\`) ON DELETE CASCADE
);`
  },
  "Database Connection (database.php)": {
    path: "/config/database.php",
    language: "php",
    code: `<?php
/**
 * BETELITE - Database Connection Config
 * Uses Object-Oriented MySQLi with Prepared Statements
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'betelite');

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed. Please verify credentials.'
    ]));
}

$conn->set_charset("utf8mb4");`
  },
  "Security Handler (security.php)": {
    path: "/config/security.php",
    language: "php",
    code: `<?php
/**
 * BETELITE - Security Middleware & Cryptographic Helpers
 */

if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}`
  },
  "Wallet Operations (functions.php)": {
    path: "/config/functions.php",
    language: "php",
    code: `<?php
/**
 * BETELITE - Essential Wallet Adjustments with Transaction Logs
 */

function change_wallet_balance($conn, $user_id, $amount, $type, $description, $reference = null, $gateway = 'wallet') {
    if (!$reference) {
        $reference = 'TRX-' . strtoupper(bin2hex(random_bytes(8)));
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT balance FROM \`wallets\` WHERE user_id = ? FOR UPDATE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $balance = $res->num_rows ? (double)$res->fetch_assoc()['balance'] : 0.0;
        $stmt->close();

        $new_balance = $balance + (double)$amount;
        if ($new_balance < 0) throw new Exception("Insufficient wallet funds.");

        $update = $conn->prepare("UPDATE \`wallets\` SET balance = ? WHERE user_id = ?");
        $update->bind_param("di", $new_balance, $user_id);
        $update->execute();

        $status = 'completed';
        $log = $conn->prepare("INSERT INTO \`transactions\` (user_id, amount, type, status, description, reference, gateway) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $log->bind_param("idsssss", $user_id, $amount, $type, $status, $description, $reference, $gateway);
        $log->execute();

        $conn->commit();
        return ['success' => true, 'new_balance' => $new_balance];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}`
  },
  "Marketplace View (marketplace.php)": {
    path: "/marketplace.php",
    language: "php",
    code: `<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Marketplace - BETELITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-white min-h-screen">
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-4">Predictions Marketplace</h1>
        <!-- Elegant glassmorphic selection grid queries predictions joins user table -->
    </div>
</body>
</html>`
  },
  "Live Match Center (live.php)": {
    path: "/live.php",
    language: "php",
    code: `<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Live Football - BETELITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-white min-h-screen">
    <!-- Real-time soccer events. Triggers setInterval AJAX calls every 5s -->
</body>
</html>`
  }
};

interface UserSession {
  userId: number;
  username: string;
  role: "admin" | "predictor" | "user";
  balance: number;
}

export default function App() {
  // Navigation State
  const [activeTab, setActiveTab] = useState<"sandbox" | "tg_mini_app" | "code_hub" | "cpanel_guide">("sandbox");
  
  // Sandbox Subviews Tab
  const [sandboxView, setSandboxView] = useState<"landing" | "login" | "register" | "dashboard" | "marketplace" | "live" | "wallet" | "predictor_lounge" | "admin_suite">("landing");
  
  // Simulated User Session
  const [session, setSession] = useState<UserSession | null>({
    userId: 2,
    username: "tipster",
    role: "predictor",
    balance: 550.00
  });

  // Simulator Data States
  const [transactions, setTransactions] = useState([
    { ref: "DEP-E7D9BC", desc: "Deposit credited via Paystack", gateway: "Paystack", amount: 500.00, date: "2026-05-23 07:11", type: "deposit" },
    { ref: "SGN-AC293B", desc: "Sign up welcome bonus!", gateway: "wallet", amount: 50.00, date: "2026-05-23 07:05", type: "deposit" }
  ]);
  
  const [predictionBundles, setPredictionBundles] = useState([
    { id: 1, title: "UEFA Champions Double Max", desc: "Real Madrid to Win + over 2.5 goals in Manchester City encounter", price: 15.00, combinedOdds: 2.85, confidence: 94, sport: "Football", predictor: "MegaTipster", isHot: true, isVip: true, status: "pending" },
    { id: 2, title: "Premier League Super Weekend Over", desc: "Both Teams To Score at Chelsea vs Arsenal with Over 1.5 goals", price: 8.00, combinedOdds: 1.95, confidence: 85, sport: "Football", predictor: "tipster", isHot: true, isVip: false, status: "pending" },
    { id: 3, title: "Euroleague Basketball Bankroll Tip", desc: "Barcelona Handicap -5.5 points score against Monaco selection", price: 20.00, combinedOdds: 2.10, confidence: 88, sport: "Basketball", predictor: "AlphaPrediction", isHot: false, isVip: true, status: "pending" }
  ]);

  const [purchasedBundles, setPurchasedBundles] = useState<typeof predictionBundles>([]);
  const [cart, setCart] = useState<typeof predictionBundles>([]);

  // Form Fields for published bundles creation
  const [newTitle, setNewTitle] = useState("");
  const [newDesc, setNewDesc] = useState("");
  const [newOdds, setNewOdds] = useState("2.15");
  const [newPrice, setNewPrice] = useState("5.00");
  const [newSport, setNewSport] = useState("Football");
  const [newConfidence, setNewConfidence] = useState("85");
  const [newVip, setNewVip] = useState(false);
  const [newHot, setNewHot] = useState(false);

  // Admin Config state
  const [platformFee, setPlatformFee] = useState("20");

  // Code Hub state
  const [selectedFileKey, setSelectedFileKey] = useState<string>("Database Schema (betelite.sql)");
  const [copiedFile, setCopiedFile] = useState(false);

  // Telegram Mini App Screen State
  const [tgScreen, setTgScreen] = useState<string>("today");

  // Live Score State Tracker
  const [liveScore, setLiveScore] = useState({ home: 2, away: 1, minute: 72 });
  const [commentaryLogs, setCommentaryLogs] = useState([
    { min: "71'", action: "Substitution Real Madrid", text: "Luka Modric enters the pitch replacing Federico Valverde to consolidate midfield control." },
    { min: "68'", action: "Yellow Card Man City", text: "Erling Haaland is booked of a critical sliding challenge on Antonio Rüdiger." }
  ]);

  // Handle live scoring tick simulations
  useEffect(() => {
    const timer = setInterval(() => {
      setLiveScore(prev => {
        const nextMin = prev.minute + 1;
        if (nextMin > 90) return { home: 0, away: 0, minute: 0 };
        
        // Randomly score a goal
        let nextHome = prev.home;
        let nextAway = prev.away;
        if (Math.random() > 0.94) {
          if (Math.random() > 0.5) {
            nextHome += 1;
            setCommentaryLogs(cl => [
              { min: `${nextMin}'`, action: "⚽ GOAL Real Madrid!", text: "Outstanding top corner strike inside the penalty box sets Madrid on fire!" },
              ...cl
            ]);
          } else {
            nextAway += 1;
            setCommentaryLogs(cl => [
              { min: `${nextMin}'`, action: "⚽ GOAL Man City!", text: "Clinical close range conversion from Erling Haaland finishes beautifully." },
              ...cl
            ]);
          }
        } else if (Math.random() > 0.9) {
          // General attempt commentary
          setCommentaryLogs(cl => [
            { min: `${nextMin}'`, action: "Foul Call Play", text: "Referee halts play due to an offside trigger or slide challenge." },
            ...cl
          ]);
        }

        return { home: nextHome, away: nextAway, minute: nextMin };
      });
    }, 5000);
    return () => clearInterval(timer);
  }, []);

  // Simulator helper: Add to cart
  const handleAddToCart = (item: typeof predictionBundles[0]) => {
    if (!session) {
      alert("Please log in to simulate purchases.");
      setSandboxView("login");
      return;
    }
    if (cart.some(c => c.id === item.id)) {
      alert("Bundle is already in your cashout cart!");
      return;
    }
    setCart(prev => [...prev, item]);
  };

  // Simulator helper: Checkout
  const handleCheckout = () => {
    if (!session) return;
    const total = cart.reduce((acc, current) => acc + current.price, 0);
    if (session.balance < total) {
      alert("Insufficient wallet balance. Simulate a Paystack deposit first!");
      setSandboxView("wallet");
      return;
    }

    // Deduct and purchase
    setSession(prev => prev ? { ...prev, balance: prev.balance - total } : null);
    setTransactions(prev => [
      {
        ref: "W_OUT-" + Math.random().toString(36).substring(2, 8).toUpperCase(),
        desc: `Bought ${cart.length} forecast bundle(s)`,
        gateway: "wallet",
        amount: -total,
        date: new Date().toISOString().replace('T', ' ').substring(0, 16),
        type: "withdrawal"
      },
      ...prev
    ]);

    setPurchasedBundles(prev => [...prev, ...cart]);
    setCart([]);
    alert(`Checkout Completed! You unlocked ${cart.length} forecaster coordinates instantly.`);
    setSandboxView("dashboard");
  };

  // Simulator helper: Deposit
  const triggerDeposit = (amountStr: string) => {
    if (!session) return;
    const val = parseFloat(amountStr);
    if (isNaN(val) || val <= 0) {
      alert("Provide a valid positive sum.");
      return;
    }

    setSession(prev => prev ? { ...prev, balance: prev.balance + val } : null);
    setTransactions(prev => [
      {
        ref: "DEP-" + Math.random().toString(36).substring(2, 8).toUpperCase(),
        desc: "Deposit credited via Paystack",
        gateway: "Paystack",
        amount: val,
        date: new Date().toISOString().replace('T', ' ').substring(0, 16),
        type: "deposit"
      },
      ...prev
    ]);
    alert(`Success! Credited ${val.toFixed(2)} to your virtual address.`);
  };

  // Simulator helper: Create prediction
  const publishPrediction = (e: React.FormEvent) => {
    e.preventDefault();
    if (!session) return;
    const bundlePrice = parseFloat(newPrice);
    const oddsRatio = parseFloat(newOdds);
    
    if (!newTitle) {
      alert("Please fill in a title.");
      return;
    }

    const itemObj = {
      id: predictionBundles.length + 1,
      title: newTitle,
      desc: newDesc || "Custom forecaster logical review.",
      price: bundlePrice || 5.00,
      combinedOdds: oddsRatio || 2.15,
      confidence: parseInt(newConfidence) || 85,
      sport: newSport,
      predictor: session.username,
      isHot: newHot,
      isVip: newVip,
      status: "pending"
    };

    setPredictionBundles(prev => [itemObj, ...prev]);
    alert("Bundle published directly to buyer boards!");
    setNewTitle("");
    setNewDesc("");
    setSandboxView("predictor_lounge");
  };

  // Copy code utility
  const copyCodeToClipboard = () => {
    navigator.clipboard.writeText(GENERATED_FILES[selectedFileKey].code);
    setCopiedFile(true);
    setTimeout(() => setCopiedFile(false), 2000);
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#020617] text-slate-100 selection:bg-blue-600 selection:text-white">
      
      {/* Platform Branding Telemetry Bar */}
      <header className="px-5 py-4 glass-header flex flex-col sm:flex-row justify-between items-center gap-3 sticky top-0 z-50">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-400 flex items-center justify-center font-bold text-white text-lg tracking-wider shadow-lg shadow-cyan-500/10 animate-glow">
            BE
          </div>
          <div>
            <span className="text-white text-lg font-bold tracking-tight">BET<span className="text-blue-500">ELITE</span></span>
            <span className="text-[10px] block font-mono text-cyan-400/80 tracking-wider">CPANEL SHARED PORTABLE SANDBOX</span>
          </div>
        </div>

        {/* Primary Tabs */}
        <div className="flex bg-white/5 rounded-full p-1 border border-white/8 text-xs font-semibold gap-1">
          <button 
            onClick={() => setActiveTab("sandbox")}
            className={`px-4 py-2 rounded-full flex items-center gap-1.5 transition-all ${activeTab === "sandbox" ? "bg-blue-600 text-white" : "hover:bg-white/5 text-slate-400"}`}
          >
            <Layers className="w-3.5 h-3.5" /> Interactive Sandbox
          </button>
          
          <button 
            onClick={() => setActiveTab("tg_mini_app")}
            className={`px-4 py-2 rounded-full flex items-center gap-1.5 transition-all ${activeTab === "tg_mini_app" ? "bg-cyan-600 text-white" : "hover:bg-white/5 text-slate-400"}`}
          >
            <Smartphone className="w-3.5 h-3.5" /> Telegram Mini-App
          </button>

          <button 
            onClick={() => setActiveTab("code_hub")}
            className={`px-4 py-2 rounded-full flex items-center gap-1.5 transition-all ${activeTab === "code_hub" ? "bg-indigo-600 text-white" : "hover:bg-white/5 text-slate-400"}`}
          >
            <FileText className="w-3.5 h-3.5" /> Sourced Code Hub
          </button>

          <button 
            onClick={() => setActiveTab("cpanel_guide")}
            className={`px-4 py-2 rounded-full flex items-center gap-1.5 transition-all ${activeTab === "cpanel_guide" ? "bg-amber-600 text-white" : "hover:bg-white/5 text-slate-400"}`}
          >
            <Globe className="w-3.5 h-3.5" /> Deploy Guide
          </button>
        </div>
      </header>

      {/* Main Sandbox Frame view */}
      <main className="flex-1 p-5 max-w-7xl mx-auto w-full">
        
        {activeTab === "sandbox" && (
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-5">
            
            {/* Left Sandbox Directory Map */}
            <div className="lg:col-span-3">
              <div className="glass-card p-5 shadow-2xl animate-fade-in">
                <span className="text-[10px] font-mono tracking-widest text-slate-400 block uppercase mb-4">NAVIGATIONAL WIREFRAME</span>
                
                <div className="space-y-1">
                  <button 
                    onClick={() => setSandboxView("landing")}
                    className={`w-full justify-start text-left text-xs py-2.5 px-3 rounded-lg flex items-center gap-2 transition-all ${sandboxView === "landing" ? "bg-blue-600/10 text-blue-400 border-l-2 border-blue-500 font-bold" : "text-slate-400 hover:bg-white/5"}`}
                  >
                    <Globe className="w-3.5 h-3.5" /> index.php (Landing Page)
                  </button>

                  <button 
                    onClick={() => setSandboxView("marketplace")}
                    className={`w-full justify-start text-left text-xs py-2.5 px-3 rounded-lg flex items-center gap-2 transition-all ${sandboxView === "marketplace" ? "bg-blue-600/10 text-blue-400 border-l-2 border-blue-500 font-bold" : "text-slate-400 hover:bg-white/5"}`}
                  >
                    <ShoppingCart className="w-3.5 h-3.5 text-cyan-400" /> marketplace.php (Card Shop)
                  </button>

                  <button 
                    onClick={() => setSandboxView("live")}
                    className={`w-full justify-start text-left text-xs py-2.5 px-3 rounded-lg flex items-center gap-2 transition-all ${sandboxView === "live" ? "bg-blue-600/10 text-blue-400 border-l-2 border-blue-500 font-bold" : "text-slate-400 hover:bg-white/5"}`}
                  >
                    <Activity className="w-3.5 h-3.5 text-red-500 animate-pulse" /> live.php (Soccer Center)
                  </button>

                  <hr className="border-white/5 my-3" />

                  {session ? (
                    <>
                      <button 
                        onClick={() => setSandboxView("dashboard")}
                        className={`w-full justify-start text-left text-xs py-2.5 px-3 rounded-lg flex items-center gap-2 transition-all ${sandboxView === "dashboard" ? "bg-blue-600/10 text-blue-400 border-l-2 border-blue-500 font-bold" : "text-slate-400 hover:bg-white/5"}`}
                      >
                        <User className="w-3.5 h-3.5 text-blue-400" /> dashboard.php (My Desk)
                      </button>

                      <button 
                        onClick={() => setSandboxView("wallet")}
                        className={`w-full justify-start text-left text-xs py-2.5 px-3 rounded-lg flex items-center gap-2 transition-all ${sandboxView === "wallet" ? "bg-blue-600/10 text-blue-400 border-l-2 border-blue-500 font-bold" : "text-slate-400 hover:bg-white/5"}`}
                      >
                        <Wallet className="w-3.5 h-3.5 text-green-400" /> wallet.php (My Cash Deck)
                      </button>

                      {session.role === "predictor" && (
                        <button 
                          onClick={() => setSandboxView("predictor_lounge")}
                          className={`w-full justify-start text-left text-xs py-2.5 px-3 rounded-lg flex items-center gap-2 transition-all ${sandboxView === "predictor_lounge" ? "bg-blue-600/10 text-blue-400 border-l-2 border-blue-500 font-bold" : "text-slate-400 hover:bg-white/5"}`}
                        >
                          <Cpu className="w-3.5 h-3.5 text-cyan-400" /> Predictor Lounge
                        </button>
                      )}

                      {session.role === "admin" && (
                        <button 
                          onClick={() => setSandboxView("admin_suite")}
                          className={`w-full justify-start text-left text-xs py-2.5 px-3 rounded-lg flex items-center gap-2 transition-all ${sandboxView === "admin_suite" ? "bg-blue-600/10 text-blue-400 border-l-2 border-blue-500 font-bold" : "text-slate-400 hover:bg-white/5"}`}
                        >
                          <Lock className="w-3.5 h-3.5 text-amber-400" /> Admin RBAC Suite
                        </button>
                      )}

                      <hr className="border-white/5 my-3" />
                      
                      <div className="p-3 bg-white/5 border border-white/5 rounded-xl text-center">
                        <span className="text-[10px] text-white/50 block uppercase font-mono mb-1">Session identity</span>
                        <div className="fw-bold text-xs text-white">@{session.username}</div>
                        <div className="text-[10px] text-sky-400 font-bold uppercase tracking-wider">{session.role}</div>
                        <button 
                          onClick={() => { setSession(null); setSandboxView("landing"); }}
                          className="w-full mt-2.5 text-[10px] text-red-400 bg-red-950/25 border border-red-500/20 py-1 rounded-md"
                        >
                          End Session
                        </button>
                      </div>
                    </>
                  ) : (
                    <div className="p-3 bg-white/5 border border-white/5 rounded-xl text-center">
                      <span className="text-[10px] tracking-wide text-white/50 block mb-2">No active sessions</span>
                      <div className="grid grid-cols-2 gap-1.5">
                        <button 
                          onClick={() => setSandboxView("login")}
                          className="bg-blue-600 text-white font-bold py-1 px-2.5 rounded text-[11px]"
                        >
                          Log In
                        </button>
                        <button 
                          onClick={() => setSandboxView("register")}
                          className="bg-slate-800 text-white font-semibold py-1 px-2.5 rounded text-[11px]"
                        >
                          Sign Up
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Right Sandbox App Core Rendering Viewport */}
            <div className="lg:col-span-9">
              <div 
                className="glass-card min-h-[580px] p-6 relative overflow-hidden animate-fade-in"
                style={{
                  backgroundImage: "radial-gradient(circle at top right, rgba(6, 182, 212, 0.15), rgba(37, 99, 235, 0.05))"
                }}
              >
                
                {sandboxView === "landing" && (
                  <div className="space-y-8 py-5">
                    <div className="max-w-xl">
                      <div className="inline-flex items-center gap-1.5 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider mb-4">
                        <Flame className="w-3.5 h-3.5 text-cyan-500" /> THE FORECASTING REVOLUTION
                      </div>
                      <h1 className="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mb-4 leading-none">
                        Win More with Verified <span className="text-cyan-400">Pro Experts</span>
                      </h1>
                      <p className="text-sm text-slate-300 mb-6 leading-relaxed">
                        Buy high-confidence sport prediction bundles from vetted, profitable hand-picked forecasters, or monetize your predictions on Africa's elite, transparent pool. Pay and cashout with seamless ease.
                      </p>
                      
                      <div className="flex flex-wrap gap-3">
                        <button 
                          onClick={() => setSandboxView("marketplace")}
                          className="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-blue-500/20 text-xs transition"
                        >
                          Explore Verified Bundles
                        </button>
                        <button 
                          onClick={() => setSandboxView("live")}
                          className="bg-slate-800 hover:bg-slate-700 text-white font-semibold px-6 py-3 rounded-xl text-xs transition border border-white/5 flex items-center gap-2"
                        >
                          Live Match Center <span className="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        </button>
                      </div>
                    </div>

                    {/* Spotlight section */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8 pt-6 border-t border-white/5 font-sans">
                      <div className="p-5 rounded-2xl glass-card flex items-center gap-3 hover:scale-[1.02] transition-transform duration-300">
                        <div className="w-10 h-10 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg">🎯</div>
                        <div>
                          <h4 className="font-bold text-xs text-white">MegaTipster Pro</h4>
                          <span className="text-[10.5px] text-green-400 font-bold uppercase tracking-wider">Win rate: 88.5%</span>
                        </div>
                      </div>
                      <div className="p-5 rounded-2xl glass-card flex items-center gap-3 hover:scale-[1.02] transition-transform duration-300">
                        <div className="w-10 h-10 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg">🔮</div>
                        <div>
                          <h4 className="font-bold text-xs text-white">Alpha Predictions</h4>
                          <span className="text-[10.5px] text-green-400 font-bold uppercase tracking-wider">Win rate: 92.1%</span>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {sandboxView === "login" && (
                  <div className="flex justify-center items-center py-10">
                    <div className="max-w-sm w-full glass-card p-8 shadow-2xl relative z-10 border-white/10">
                      <h3 className="text-lg font-bold text-center text-white mb-4 font-sans">Account Login</h3>
                      
                      <div className="space-y-4">
                        <div>
                          <label className="block text-[10px] font-bold text-slate-400 mb-1 font-sans">USERNAME OR EMAIL</label>
                          <input type="text" defaultValue="tipster" className="w-full glass-input text-white text-xs transition focus:outline-none focus:border-blue-500" />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-slate-400 mb-1 font-sans">PASSWORD</label>
                          <input type="password" value="••••••••••••" className="w-full glass-input text-white text-xs transition focus:outline-none focus:border-blue-500" />
                        </div>
                        <button 
                          onClick={() => {
                            setSession({ userId: 2, username: "tipster", role: "predictor", balance: 50.00 });
                            setSandboxView("dashboard");
                          }}
                          className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs shadow-lg font-sans transition-all duration-300"
                        >
                          Sign In (Simulate User)
                        </button>
                      </div>

                      <div className="text-center mt-5 pt-4 border-t border-white/5">
                        <span className="text-[10.5px] text-slate-400 block mb-2 font-sans">Or log in as developer Admin:</span>
                        <button 
                          onClick={() => {
                            setSession({ userId: 1, username: "admin", role: "admin", balance: 10000.00 });
                            setSandboxView("dashboard");
                          }}
                          className="bg-amber-500 hover:bg-amber-600 text-black text-[10px] font-bold uppercase py-1.5 px-4 rounded-full shadow transition-all duration-300"
                        >
                          Log In as Admin
                        </button>
                      </div>
                    </div>
                  </div>
                )}

                {sandboxView === "register" && (
                  <div className="flex justify-center items-center py-5">
                    <div className="max-w-sm w-full glass-card p-8 shadow-2xl relative z-10 border-white/10">
                      <h3 className="text-lg font-bold text-center text-white mb-4 font-sans">Create Account</h3>
                      <div className="space-y-4">
                        <div>
                          <label className="block text-[10px] font-bold text-slate-400 mb-1 font-sans">USERNAME</label>
                          <input type="text" placeholder="e.g. tipster" className="w-full glass-input text-white text-xs focus:outline-none" />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-slate-400 mb-1 font-sans">EMAIL ADDRESS</label>
                          <input type="email" placeholder="e.g. predictor@mail.com" className="w-full glass-input text-white text-xs focus:outline-none" />
                        </div>
                        <button 
                          onClick={() => {
                            setSession({ userId: 3, username: "new_tipster", role: "user", balance: 2.00 });
                            setSandboxView("dashboard");
                          }}
                          className="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 rounded-xl text-xs transition-all duration-300 font-sans shadow-lg"
                        >
                          Register and claim Welcome Bonus
                        </button>
                      </div>
                    </div>
                  </div>
                )}

                {sandboxView === "dashboard" && session && (
                  <div className="space-y-6 animate-fade-in font-sans">
                    <div className="flex justify-between items-center glass-card p-5 border-white/8">
                      <div>
                        <span className="text-3xs text-slate-400 block uppercase font-mono tracking-widest">Welcome back</span>
                        <h4 className="fw-bold text-lg text-white font-sans font-semibold">@{session.username}</h4>
                      </div>
                      <div className="text-right">
                        <span className="text-[10px] text-slate-400 block uppercase font-mono tracking-widest leading-none mb-1">My Code</span>
                        <span className="badge bg-indigo-500/20 text-indigo-400 border border-indigo-500/25 text-xs font-mono font-bold px-2 py-0.5 rounded-md">REF-82C39B</span>
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                      <div className="glass-card p-5 hover:border-blue-500/30 transition-all duration-300">
                        <span className="text-[10px] text-slate-400 uppercase font-bold tracking-widest block font-mono">Wallet Balance</span>
                        <div className="text-2xl font-bold font-mono text-white mt-1.5">${session.balance.toFixed(2)}</div>
                        <button onClick={() => setSandboxView("wallet")} className="text-xs text-blue-400 mt-2 hover:text-blue-300 hover:underline transition-colors duration-250 inline-block">&rarr; Quick Fund</button>
                      </div>
                      <div className="glass-card p-5 hover:border-cyan-500/30 transition-all duration-300">
                        <span className="text-[10px] text-slate-400 uppercase font-bold tracking-widest block font-mono">Purchased Bundles</span>
                        <div className="text-2xl font-bold font-mono text-white mt-1.5">{purchasedBundles.length} Unlocked</div>
                        <span className="text-[10px] text-slate-400 leading-none">Vetted tips ready</span>
                      </div>
                      <div className="glass-card p-5 hover:border-indigo-500/30 transition-all duration-300">
                        <span className="text-[10px] text-slate-400 uppercase font-bold tracking-widest block font-mono">Cart items</span>
                        <div className="text-2xl font-bold font-mono text-cyan-400 mt-1.5">{cart.length} items</div>
                        <button onClick={() => setSandboxView("marketplace")} className="text-xs text-cyan-400 mt-2 hover:text-cyan-300 hover:underline transition-colors duration-250 inline-block">&rarr; Show Cart</button>
                      </div>
                    </div>

                    <div className="glass-card p-6 mt-4">
                      <h4 className="text-sm font-bold text-white mb-3 flex items-center gap-2 font-sans">
                        <CheckCircle className="w-4 h-4 text-green-400" /> My Active Purchased Forecasts
                      </h4>
                      {purchasedBundles.length === 0 ? (
                        <div className="text-center py-8 text-xs text-slate-400 font-sans">
                          No forecast bundles unlocked yet. Visit predictions shop to browse max-win tips!
                        </div>
                      ) : (
                        <div className="space-y-2.5">
                          {purchasedBundles.map(item => (
                            <div key={item.id} className="flex justify-between items-center p-3.5 rounded-xl bg-white/3 text-xs border border-white/5 hover:border-white/10 transition-colors duration-250">
                              <div>
                                <span className="font-bold text-white block">{item.title}</span>
                                <span className="text-[10.5px] text-slate-400">Analysis: {item.desc}</span>
                              </div>
                              <div className="text-right">
                                <span className="badge bg-green-500/20 text-green-400 border border-green-500/25 px-2 py-0.5 rounded text-2xs font-mono font-bold select-all">UNLOCKED</span>
                                <span className="font-bold text-sky-400 font-mono block mt-1.5">{item.combinedOdds}x odds</span>
                              </div>
                            </div>
                          ))}
                        </div>
                      )}
                    </div>
                  </div>
                )}

                {sandboxView === "marketplace" && (
                  <div className="space-y-6 animate-fade-in font-sans">
                    <div className="flex justify-between items-center flex-wrap gap-3 pb-3 border-b border-white/5">
                      <div>
                        <h2 className="text-xl font-bold text-white font-sans">Forecasters Shop</h2>
                        <p className="text-xs text-slate-400">Vetted sport forecasters offering high confidence odds.</p>
                      </div>
                      {cart.length > 0 && (
                        <div className="flex items-center gap-3 bg-cyan-950/20 p-2.5 rounded-xl border border-cyan-500/20">
                          <div>
                            <span className="text-3xs text-cyan-400 block uppercase font-mono">Grand Total</span>
                            <span className="font-bold font-mono text-sm">${cart.reduce((a, b) => a + b.price, 0).toFixed(2)}</span>
                          </div>
                          <button onClick={handleCheckout} className="bg-cyan-500 hover:bg-cyan-600 text-black font-bold text-xs py-2 px-4 rounded-lg transition-colors">
                            Checkout
                          </button>
                        </div>
                      )}
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      {predictionBundles.map(bundle => (
                        <div key={bundle.id} className="p-5 rounded-2xl glass-card border-white/8 hover:border-cyan-500/30 transition-all duration-300 hover:scale-[1.01] flex flex-col justify-between">
                          <div>
                            <div className="flex justify-between items-center mb-2.5">
                              <span className="badge bg-white/5 text-slate-300 text-2xs uppercase border border-white/5 px-2 py-0.5 rounded">{bundle.sport}</span>
                              <div className="flex gap-1">
                                {bundle.isHot && <span className="badge bg-red-550/20 text-red-400 border border-red-500/25 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded">HOT</span>}
                                {bundle.isVip && <span className="badge bg-amber-550/20 text-amber-400 border border-amber-500/25 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded">VIP</span>}
                              </div>
                            </div>

                            <h3 className="font-bold text-sm text-white mb-1.5 font-sans">{bundle.title}</h3>
                            <p className="text-[11px] text-slate-400 leading-relaxed max-w-sm mb-4 italic">"{bundle.desc}"</p>
                          </div>

                          <div className="border-t border-white/5 pt-3.5 flex justify-between items-center font-sans">
                            <div>
                              <span className="text-[9.5px] uppercase block tracking-wider text-slate-500 font-semibold mb-0.5 font-mono">Price to unlock</span>
                              <span className="font-bold font-mono text-sm text-cyan-400">${bundle.price.toFixed(2)}</span>
                            </div>
                            <div className="flex items-center gap-3">
                              <div className="text-right">
                                <span className="text-[10px] text-slate-400 block leading-none font-mono">Odds: <strong>{bundle.combinedOdds}x</strong></span>
                                <span className="text-[10px] text-green-400 font-bold leading-normal">{bundle.confidence}% Conf.</span>
                              </div>
                              <button 
                                onClick={() => handleAddToCart(bundle)}
                                className="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] py-2 px-4 rounded-xl transition duration-200"
                              >
                                Buy Tip
                              </button>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {sandboxView === "live" && (
                  <div className="space-y-6 animate-fade-in font-sans">
                    <div className="flex justify-between items-center flex-wrap gap-2 pb-3 border-b border-white/5">
                      <div>
                        <h2 className="text-xl font-bold text-white flex items-center gap-2 font-sans">
                          <Activity className="w-4 h-4 text-red-500 animate-pulse" /> Live Scoreboard
                        </h2>
                        <p className="text-xs text-slate-400">Match updates feed refresh automatic every 5s.</p>
                      </div>
                      <span className="text-[10.5px] font-mono text-cyan-400 bg-cyan-950/20 px-3 py-1.5 rounded-lg border border-cyan-500/20 font-bold uppercase tracking-wider">AUTO FREQUENCIES ENABLED</span>
                    </div>

                    {/* Active Match score card */}
                    <div className="glass-card p-6 shadow-2xl text-center relative overflow-hidden animate-glow">
                      <div className="text-[10px] uppercase font-bold tracking-widest text-slate-400 mb-4 font-mono">🏆 UEFA CHAMPIONS LEAGUE PLAYOFF (DEMO)</div>
                      
                      <div className="flex justify-between items-center max-w-md mx-auto py-2">
                        <div className="w-2/5 text-right">
                          <h4 className="fw-bold text-base text-white font-sans font-semibold">Real Madrid</h4>
                          <span className="text-[10px] text-slate-400 block font-mono uppercase tracking-wider">Home Arena</span>
                        </div>
                        
                        <div className="w-1/5 text-center">
                          <div className="text-2xl font-bold font-mono text-cyan-400 bg-black/40 px-3 py-1.5 rounded-xl border border-white/5 shadow-inner">{liveScore.home} - {liveScore.away}</div>
                          <span className="badge bg-red-600/20 text-red-500 border border-red-500/20 text-3xs font-mono font-bold mt-2.5 inline-block px-2 py-0.5 rounded uppercase tracking-wider animate-pulse">LIVE {liveScore.minute}'</span>
                        </div>

                        <div className="w-2/5 text-left">
                          <h4 className="fw-bold text-base text-white font-sans font-semibold">Manchester City</h4>
                          <span className="text-[10px] text-slate-400 block font-mono uppercase tracking-wider">Visitor Arena</span>
                        </div>
                      </div>

                      {/* Possession indicator */}
                      <div className="mt-5 max-w-xs mx-auto text-xs font-sans">
                        <div className="flex justify-between text-[10px] text-slate-450 mb-1.5 font-bold font-mono">
                          <span>Possession: 55%</span>
                          <span>45%</span>
                        </div>
                        <div className="h-2 rounded-full bg-slate-800/80 overflow-hidden flex">
                          <div className="bg-cyan-500 h-full transition-all duration-300" style={{ width: "55%" }}></div>
                          <div className="bg-blue-600 h-full w-full"></div>
                        </div>
                      </div>
                    </div>

                    {/* Commentary Flow lists */}
                    <div className="glass-card p-5">
                      <h4 className="text-xs uppercase font-bold text-slate-300 tracking-wider mb-3.5 font-sans flex items-center gap-1.5">💬 Commentary Events Stream</h4>
                      
                      <div className="space-y-2.5 max-h-[180px] overflow-y-auto pr-1">
                        {commentaryLogs.map((item, idx) => (
                          <div key={idx} className="p-3 bg-white/3 rounded-xl flex items-start gap-2.5 text-xs border border-white/4">
                            <span className="badge bg-cyan-950/40 text-cyan-400 font-bold text-[10px] font-mono px-2 py-0.5 rounded tracking-wider">{item.min}</span>
                            <div>
                              <strong className="text-white block font-semibold mb-0.5 font-sans text-[11px]">{item.action}</strong>
                              <p className="text-slate-400 text-xs leading-relaxed font-sans">{item.text}</p>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                )}

                {sandboxView === "wallet" && (
                  <div className="space-y-6 animate-fade-in font-sans">
                    <div className="flex justify-between items-center border-b border-white/5 pb-3">
                      <div>
                        <h2 className="text-xl font-bold text-white font-sans">Virtual Transact Deck</h2>
                        <p className="text-xs text-slate-400 font-semibold mb-1">Fund virtual balance to buy predictions bundles instantly.</p>
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      {/* Deposits */}
                      <div className="glass-card p-6 flex flex-col justify-between border-white/8">
                        <div>
                          <span className="text-[10px] text-slate-400 block uppercase font-bold tracking-widest font-mono mb-2">FUND PAYMENTS DIALOG</span>
                          <p className="text-xs text-slate-350 leading-relaxed font-sans">Choose arbitrary USD amount to simulate transaction verifications via Paystack Webhook API mock pipelines.</p>
                        </div>
                        
                        <div className="mt-5">
                          <div className="grid grid-cols-3 gap-2">
                            <button onClick={() => triggerDeposit("10")} className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-1 rounded-xl text-xs font-mono transition-colors">+$10 USD</button>
                            <button onClick={() => triggerDeposit("50")} className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-1 rounded-xl text-xs font-mono transition-colors">+$50 USD</button>
                            <button onClick={() => triggerDeposit("100")} className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-1 rounded-xl text-xs font-mono transition-colors">+$100 USD</button>
                          </div>
                        </div>
                      </div>

                      {/* Withdrawals block */}
                      <div className="glass-card p-6 flex flex-col justify-between border-white/8">
                        <div>
                          <span className="text-[10px] text-slate-400 block uppercase font-bold tracking-widest font-mono mb-2">LOCAL WIRE CASHOUT OUTS</span>
                          <p className="text-xs text-slate-350 leading-relaxed font-sans">Trigger local bank transfer and submit payload details below for administrator review queue tests.</p>
                        </div>
                        
                        <div className="mt-5">
                          <button onClick={() => alert("Withdraw requests submitted.")} className="w-full bg-slate-800 hover:bg-slate-705 text-white border border-white/5 font-semibold py-2.5 rounded-xl text-xs transition duration-200">
                            Simulate Bank Payout Form
                          </button>
                        </div>
                      </div>
                    </div>

                    {/* Historical Logs summary */}
                    <div className="glass-card p-5">
                      <h4 className="text-xs uppercase font-bold text-slate-300 mb-3.5 font-mono">Transaction Ledger Records ($)</h4>
                      
                      <div className="space-y-2.5">
                        {transactions.map((t, idx) => (
                          <div key={idx} className="flex justify-between items-center text-xs p-3 rounded-xl bg-white/3 border border-white/4">
                            <div>
                              <span className="font-mono text-slate-350 font-bold block">{t.ref}</span>
                              <span className="text-3xs text-slate-400 leading-none">{t.desc} &bull; {t.gateway}</span>
                            </div>
                            <span className={`font-mono font-bold text-sm ${t.amount >= 0 ? "text-green-400" : "text-red-400"}`}>
                              {t.amount >= 0 ? "+" : ""}${t.amount.toFixed(2)}
                            </span>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                )}

                {sandboxView === "predictor_lounge" && (
                  <div className="space-y-6 animate-fade-in font-sans">
                    <h2 className="text-xl font-bold text-white flex items-center gap-1.5 font-sans">
                      <Cpu className="w-5 h-5 text-indigo-400" /> Predictor Lounge Box
                    </h2>

                    <form onSubmit={publishPrediction} className="glass-card p-6 space-y-4">
                      <span className="text-[10px] text-slate-400 block uppercase font-bold tracking-widest font-mono">Publish New Bundle For Sale</span>
                      
                      <div>
                        <label className="block text-[10px] uppercase font-bold text-slate-400 mb-1 font-mono">Bundle Title</label>
                        <input 
                          type="text" 
                          value={newTitle}
                          onChange={(e) => setNewTitle(e.target.value)}
                          placeholder="e.g. Champions Double Max Draw Win"
                          className="w-full glass-input text-white text-xs transition duration-200 focus:outline-none focus:border-indigo-505" 
                        />
                      </div>

                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <label className="block text-[10px] uppercase font-bold text-slate-400 mb-1 font-mono">COMBINED ODDS (X)</label>
                          <input 
                            type="number" 
                            step="0.01" 
                            value={newOdds}
                            onChange={(e) => setNewOdds(e.target.value)}
                            className="w-full glass-input text-white text-xs transition duration-200 focus:outline-none focus:border-indigo-505" 
                          />
                        </div>
                        <div>
                          <label className="block text-[10px] uppercase font-bold text-slate-400 mb-1 font-mono">MARKUP PRICE ($)</label>
                          <input 
                            type="number" 
                            step="0.01" 
                            value={newPrice}
                            onChange={(e) => setNewPrice(e.target.value)}
                            className="w-full glass-input text-white text-xs transition duration-200 focus:outline-none focus:border-indigo-505" 
                          />
                        </div>
                      </div>

                      <button type="submit" className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl text-xs transition duration-300 shadow-lg">
                        Submit Tip onto Marketplace listings
                      </button>
                    </form>
                  </div>
                )}

                {sandboxView === "admin_suite" && (
                  <div className="space-y-6 animate-fade-in font-sans">
                    <h2 className="text-xl font-bold text-white font-sans">💼 Administration RBAC controls</h2>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="glass-card p-5 border-white/8 shadow-xl flex flex-col justify-between hover:scale-[1.01] transition-transform duration-300">
                        <span className="text-2xs text-slate-400 uppercase font-bold block mb-2">Commissions percentages configurations</span>
                        <div className="flex gap-2.5 items-center">
                          <input 
                            type="number" 
                            value={platformFee}
                            onChange={(e) => setPlatformFee(e.target.value)}
                            className="bg-white/5 border border-white/8 text-white text-xs p-2 rounded-lg w-16" 
                          />
                          <span className="text-xs text-white/50">% cut on all unlocks</span>
                        </div>
                        <button onClick={() => alert("Commissions set on disk successfully!")} className="bg-amber-500 hover:bg-amber-600 text-black font-bold text-3xs uppercase py-2 px-3 mt-3.5 rounded-lg border-0 tracking-wider">Apply ratios</button>
                      </div>

                      <div className="glass-card p-5 border-white/8 shadow-xl flex flex-col justify-between hover:scale-[1.01] transition-transform duration-300">
                        <span className="text-[10px] text-slate-400 font-mono uppercase font-bold tracking-widest block mb-2.5">Withdrawals Log validation</span>
                        <span className="text-xs text-slate-450 block mb-3 leading-relaxed">Exhaustive ledger items requiring bank approvals toggles.</span>
                        <button onClick={() => alert("Payout approvals logs completed.")} className="bg-slate-800 hover:bg-slate-700 font-semibold text-white text-xs py-2 px-3 rounded-lg border border-white/5">Open payout files queue &rarr;</button>
                      </div>
                    </div>
                  </div>
                )}

              </div>
            </div>

          </div>
        )}

        {/* Telegram Mini App Smartphone simulation wrapper */}
        {activeTab === "tg_mini_app" && (
          <div className="max-w-md mx-auto py-5 flex flex-col items-center font-sans">
            <h2 className="text-center font-bold text-white mb-2 leading-none font-sans">Telegram Mini App Webview Frame</h2>
            <p className="text-center text-xs text-slate-400 mb-5 leading-normal max-w-sm">Previewing the native mobile UI screens built with CSS/JS matches. Select the viewport target bottom tab to load pages.</p>
            
            {/* Phone container mockup */}
            <div className="w-[340px] h-[640px] rounded-[40px] bg-slate-950 p-3 border-8 border-slate-800 relative shadow-2xl overflow-hidden flex flex-col justify-between animate-fade-in animate-glow">
              
              {/* Ear Speaker camera notch */}
              <div className="absolute top-0 left-1/2 transform -translate-x-1/2 bg-[#111] w-28 h-5 rounded-b-2xl z-50 flex items-center justify-center">
                <div className="w-10 h-1 bg-slate-800 rounded-full"></div>
              </div>

              {/* Status Bar */}
              <div className="flex justify-between items-center text-[10px] text-gray-400 px-4 pt-4 pb-2 z-10 w-full font-mono">
                <span>07:33 AM</span>
                <span>TG Mini bot (BETELITE)</span>
                <span>📶🔋</span>
              </div>

              {/* IFrame Viewport of Mini App */}
              <div className="flex-1 overflow-y-auto bg-[#0d1622] scrollbar-thin">
                {tgScreen === "today" && (
                  <div className="p-4 animate-fade-in">
                    <div className="flex items-center justify-between mb-4 pb-1.5 border-b border-gray-800 text-xs">
                      <span className="font-bold uppercase tracking-widest text-sky-400 font-mono">🔥 TODAY'S HOT TIPS</span>
                      <span className="text-gray-400 text-3xs font-mono">23 May</span>
                    </div>
                    <div className="space-y-3">
                      <div className="glass-card p-4 rounded-xl border-white/5 text-xs">
                        <div className="flex justify-between items-center mb-2">
                          <span className="text-sky-400 font-bold font-sans">Football &bull; UEFA</span>
                          <span className="badge bg-amber-500/20 text-amber-400 text-[9px] font-bold px-1.5 py-0.5 rounded">HOT BUNDLE</span>
                        </div>
                        <h4 className="font-bold text-white mb-1.5 leading-normal font-sans">Man City vs Real Madrid</h4>
                        <div className="flex justify-between border-t border-white/5 pt-2 mt-2 text-gray-400 text-[11px] font-mono">
                          <span>Pick: <strong className="text-white">Over 2.5 Goals</strong></span>
                          <span className="text-sky-400 font-bold">Odds: 1.85</span>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {tgScreen === "yesterday" && (
                  <div className="p-4 animate-fade-in">
                    <div className="flex items-center justify-between mb-4 pb-1.5 border-b border-gray-800 text-xs">
                      <span className="font-bold uppercase tracking-widest text-green-400 font-mono">✅ YESTERDAY'S WINS</span>
                      <span className="text-gray-400 text-3xs font-mono">22 May</span>
                    </div>
                    <div className="space-y-3">
                      <div className="glass-card p-4 rounded-xl border-white/5 text-xs border-l-4 border-green-500">
                        <h4 className="font-bold text-white leading-normal font-sans">Chelsea vs Arsenal</h4>
                        <div className="flex justify-between text-gray-400 mt-2 border-t border-white/5 pt-2 text-[11px] font-mono">
                          <span>Pick: Over 1.5 (2-2 FT)</span>
                          <span className="text-green-450 font-bold uppercase">Won</span>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {tgScreen === "vip" && (
                  <div className="p-5 text-center py-12 animate-fade-in">
                    <div className="text-3xl mb-3">💎</div>
                    <h4 className="font-bold text-white text-sm mb-1.5 font-sans">VIP Premium Circle</h4>
                    <p className="text-[11px] text-gray-400 max-w-xs mb-5 font-sans leading-relaxed">Get pushed live FORECASTINGS alerts directly inside Telegram groups chat messages.</p>
                    <button className="w-full bg-amber-500 hover:bg-amber-600 text-black font-bold p-3 rounded-xl text-xs uppercase tracking-wider font-sans leading-normal transition duration-200">Subscribe for $49.99</button>
                  </div>
                )}

                {tgScreen === "referrals" && (
                  <div className="p-5 text-center py-12 animate-fade-in">
                    <div className="text-3xl mb-3">🤝</div>
                    <h4 className="font-bold text-white text-sm mb-1.5 font-sans">Affiliates Dashboard</h4>
                    <p className="text-[11px] text-gray-400 max-w-xs mb-5 font-sans leading-relaxed">Acquire instant $5 commissions from each forecaster referral signup payouts instantly!</p>
                    <div className="bg-black/40 p-2.5 rounded-xl text-xs font-mono select-all text-sky-450 border border-white/5 tracking-wider">REF-INVITE-PRO</div>
                  </div>
                )}
              </div>

              {/* Telegram Phone navigation menu */}
              <div className="glass-nav border-t border-white/5 p-2 grid grid-cols-4 text-center text-[10px] text-gray-400 z-10 font-bold">
                <button onClick={() => setTgScreen("today")} className={`p-1 flex flex-col items-center gap-0.5 ${tgScreen === "today" ? "text-sky-400" : "hover:text-white"}`}>
                  <span>🔥</span> Today
                </button>
                <button onClick={() => setTgScreen("yesterday")} className={`p-1 flex flex-col items-center gap-0.5 ${tgScreen === "yesterday" ? "text-sky-400" : "hover:text-white"}`}>
                  <span>✅</span> Res.
                </button>
                <button onClick={() => setTgScreen("vip")} className={`p-1 flex flex-col items-center gap-0.5 ${tgScreen === "vip" ? "text-sky-400" : "hover:text-white"}`}>
                  <span>💎</span> VIP
                </button>
                <button onClick={() => setTgScreen("referrals")} className={`p-1 flex flex-col items-center gap-0.5 ${tgScreen === "referrals" ? "text-sky-400" : "hover:text-white"}`}>
                  <span>🤝</span> Refs
                </button>
              </div>

            </div>
          </div>
        )}

        {/* Code explorer tab */}
        {activeTab === "code_hub" && (
          <div className="grid grid-cols-1 md:grid-cols-12 gap-5 py-5 h-[580px] font-sans">
            {/* Folder trees selector */}
            <div className="md:col-span-4 glass-card p-5 overflow-y-auto shadow-xl">
              <span className="text-[10px] uppercase font-mono tracking-widest block text-slate-400 mb-4">PRODUCTION BUNDLE EXPORT CODES</span>
              <div className="space-y-1.5 text-xs">
                {Object.keys(GENERATED_FILES).map(key => (
                  <button 
                    key={key}
                    onClick={() => { setSelectedFileKey(key); setCopiedFile(false); }}
                    className={`w-full text-left py-2.5 px-3 rounded-xl flex items-center justify-between gap-1 transition-all ${selectedFileKey === key ? "bg-indigo-600/15 text-indigo-400 font-bold border-l-2 border-indigo-500" : "text-slate-450 hover:bg-white/5"}`}
                  >
                    <span>{key}</span>
                    <span className="text-[9px] font-mono text-slate-500">{GENERATED_FILES[key].language.toUpperCase()}</span>
                  </button>
                ))}
              </div>
            </div>

            {/* Syntax styled browser render frame */}
            <div className="md:col-span-8 flex flex-col glass-card p-5 shadow-2xl">
              <div className="flex justify-between items-center mb-4">
                <div>
                  <span className="text-white text-xs font-semibold block leading-none font-sans">{selectedFileKey}</span>
                  <span className="text-[10px] font-mono text-indigo-400 block mt-1.5">Path: {GENERATED_FILES[selectedFileKey].path}</span>
                </div>
                <button 
                  onClick={copyCodeToClipboard}
                  className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-1.5 transition leading-normal"
                >
                  <Copy className="w-3.5 h-3.5" /> {copiedFile ? "Copied!" : "Copy Source"}
                </button>
              </div>
              <pre className="flex-1 bg-black/45 p-4 text-xs font-mono text-slate-300 select-all overflow-y-auto border border-white/5 rounded-xl whitespace-pre scrollbar-thin">
                {GENERATED_FILES[selectedFileKey].code}
              </pre>
            </div>
          </div>
        )}

        {/* Deploy Guide */}
        {activeTab === "cpanel_guide" && (
          <div className="p-6 max-w-2xl mx-auto glass-card shadow-2xl animate-fade-in font-sans">
            <h2 className="font-bold text-white text-xl mb-4 flex items-center gap-2 font-sans">
              <Info className="w-5 h-5 text-amber-500" /> cPanel & Apache Deployment Steps
            </h2>

            <div className="space-y-4 text-sm text-slate-300">
              <div className="p-4 bg-white/3 border border-white/5 rounded-xl hover:border-white/10 transition-colors">
                <strong className="text-white font-sans font-semibold">1 &bull; Create the MySQL Database using phpMyAdmin</strong>
                <p className="text-xs text-slate-400 mt-1.5 leading-relaxed font-sans">
                  Export the MySQL schema definition straight from the <strong>betelite.sql</strong> file inside our <strong>Sourced Code Hub</strong> tab, then run/execute the queries inside your cPanel phpMyAdmin console.
                </p>
              </div>

              <div className="p-4 bg-white/3 border border-white/5 rounded-xl hover:border-white/10 transition-colors">
                <strong className="text-white font-sans font-semibold">2 &bull; Update /config/database.php credentials</strong>
                <p className="text-xs text-slate-400 mt-1.5 leading-relaxed font-sans">
                  Modify the database hostname, root username and root passwords within your cPanel file manager inside the <strong>database.php</strong> config.
                </p>
              </div>

              <div className="p-4 bg-white/3 border border-white/5 rounded-xl hover:border-white/10 transition-colors">
                <strong className="text-white font-sans font-semibold">3 &bull; Upload Files via FTP</strong>
                <p className="text-xs text-slate-400 mt-1.5 leading-relaxed font-sans">
                  Simply bundle and drag all project folder directories straight into your public_html folder under Apache/LiteSpeed. Done! No build tools or Node.js required on shared servers.
                </p>
              </div>
            </div>
          </div>
        )}

      </main>

      {/* Footer footer information */}
      <footer className="py-4 border-t border-white/5 text-center text-slate-500 text-xs">
        <p>BETELITE cPanel Shared Housing compliance sandbox. Vetted payments & predictions mock integration.</p>
      </footer>

    </div>
  );
}
