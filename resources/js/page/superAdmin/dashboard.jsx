import React, { useEffect, useState } from "react";
import axios from "axios";

// Recharts
import {
  BarChart,
  Bar,
  CartesianGrid,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer
} from "recharts";

// Doughnut Chart
import { Doughnut } from "react-chartjs-2";
import { Chart as ChartJS, ArcElement, Tooltip as ChartTooltip, Legend } from "chart.js";
ChartJS.register(ArcElement, ChartTooltip, Legend);

export default function DashboardCharts() {
  const [stats, setStats] = useState(null);

  const fetchStats = async () => {
    try {
      const res = await axios.get("/admin/dashboardStatistiques");

      setStats(res.data);
    } catch (err) {
      console.log("Erreur chargement stats", err);
    }
  };

  useEffect(() => {
    fetchStats();
  }, []);

  if (!stats) return <p>Chargement...</p>;

  /** ----- BAR CHART DATA ----- */
  const barData = [
    { name: "Autorités", value: stats.autorites },
    { name: "Secteurs", value: stats.secteurs },
    { name: "Admins", value: stats.admins },
  ];

  /** ----- DOUGHNUT DATA ----- */
  const doughnutData = {
    labels: ["Unité", "Ville", "Incident"],
    datasets: [
      {
        data: [stats.unites, stats.villes, stats.incidents],
        backgroundColor: ["#2563eb", "#10b981", "#f59e0b"],
        hoverBackgroundColor: ["#1d4ed8", "#059669", "#d97706"],
      },
    ],
  };

  return (
    <div style={{ padding: "20px" }}>

      {/* BAR CHART */}
      <h3>Statistiques : Autorité / Secteur / Admin</h3>
      <div style={{ width: "100%", height: 350, marginBottom: 40 }}>
        <ResponsiveContainer>
          <BarChart data={barData}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="name" />
            <YAxis />
            <Tooltip />
            <Bar dataKey="value" fill="#3b82f6" radius={[6, 6, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </div>

      {/* DOUGHNUT */}
      <h3>Répartition : Unités / Villes / Incidents</h3>
      <div style={{ width: "350px", margin: "auto" }}>
        <Doughnut data={doughnutData} />
      </div>
    </div>
  );
}
