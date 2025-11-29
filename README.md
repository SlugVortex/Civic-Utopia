<div align="center">
  <img src="https://i.ibb.co/WWfY2zk2/image.png" alt="CivicUtopia Banner" width="100%">
</div>

# 🏛️ CivicUtopia: The AI-Powered "Digital Town Square" 🗳️

[![Microsoft Azure](https://img.shields.io/badge/Built%20with-Microsoft%20Azure-0078D4?style=for-the-badge&logo=microsoftazure)](https://azure.microsoft.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)
[![Laravel v10.x](https://img.shields.io/badge/Laravel-v10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Built with ❤️](https://img.shields.io/badge/Built%20with-❤️-ff69b4.svg?style=for-the-badge)](https://github.com/SlugVortex/CivicUtopia)

---

## 🌟 Introduction

**CivicUtopia** is an AI-powered "Digital Town Square" designed to modernize democracy. It solves the problem that government is too complex, too boring, and too inaccessible for the average citizen.

Unlike traditional government portals (which are just static PDF dumps), CivicUtopia is alive. It combines social networking with collaborative productivity and agentic AI to turn 100-page legal bills into "Explain Like I'm 5" summaries, allow communities to annotate policy in real-time, and let citizens chat directly with digital twins of their political candidates.

---

## 📝 Table of Contents

1. [Introduction](#-introduction)
2. [The Problem](#️-the-problem)
3. [Our Solution](#-our-solution)
4. [Key Features](#-key-features)
5. [System Architecture](#️-system-architecture)
6. [Technology Stack](#️-technology-stack)
7. [Quick Start](#-quick-start)
8. [Project Screenshots](#-project-screenshots)
9. [Responsible AI Commitment](#-responsible-ai-commitment)
10. [Team Members](#-team-members)
11. [License](#-license)

---

## ⚠️ The Problem

Community members often struggle to find accurate, timely information about local policies, events, and services. Traditional channels lack interactivity and personalization, leading to disengagement and misinformation. Citizens feel disconnected from their representatives, confused by legalese, and powerless to report simple infrastructure issues.

---

## 💡 Our Solution

CivicUtopia is a unified civic engagement platform that empowers communities to access local government information, participate in discussions, and receive personalized updates using Azure AI services.

It achieves three goals:
*   **Demystification:** It turns legal jargon into simple language and local dialects (e.g., Jamaican Patois).
*   **Inclusion:** It allows users to navigate the entire site using voice commands and listen to any content via text-to-speech.
*   **Collaboration:** It fosters healthy debate through AI-moderated comments and collaborative document annotation.

---

## ✨ Key Features

### 1. The "Civic Guide" (Agentic Voice Navigator) 🧭
An always-on AI companion that allows users (especially the visually impaired) to navigate and control the entire website using natural voice commands.
*   **How it works:** You ask *"Take me to the ballot box"* or *"Read this page"*. The browser captures audio, sends it to **Azure Speech SDK**, and **Azure OpenAI** interprets the intent to execute clicks, scrolls, and navigation automatically.

### 2. The Ballot Box (Law Decoder) 🗳️
A tool that translates complex legal referendums into "Explain Like I'm 5" summaries and local dialects.
*   **How it works:** **Azure OpenAI** rewrites legal text into Plain English, Jamaican Patois, and Pros/Cons lists. **Azure AI Speech** reads the Patois breakdown aloud for accessibility.

### 3. The AI Council (Multi-Agent Chat System) 🤖
A real-time social feed where users can summon specialized AI Agents to fact-check or debate posts.
*   **How it works:** A user tags `@FactChecker` or `@Historian`. A background job wakes up the Agent, which uses **Bing Search** to find real-time data and **Azure OpenAI** to formulate a persona-based reply. All comments are filtered by **Azure Content Safety**.

### 4. Candidate Compass (Politician Analyzer) 🧭
An automated research tool that builds profiles for politicians and allows users to chat with their "Digital Twins."
*   **How it works:** You type a name, and the system auto-researches their manifesto using **Bing Search**. Users can then "Deep Dive" into specific stances (Crime, Economy) or chat with the candidate to ask specific policy questions.

### 5. Civic Lens (Infrastructure Reporter) 📸
A tool for citizens to report infrastructure issues (potholes, leaks) by simply taking a photo.
*   **How it works:** **Azure AI Vision** scans the uploaded photo to detect objects (e.g., "Asphalt," "Pothole"). **Azure OpenAI** then drafts a formal complaint letter to the correct government agency based on the image tags and GPS location.

### 6. Legal Library (Smart Document Reader) 📚
A "GitHub for Laws" where users upload PDFs, and the AI parses them for interactive reading.
*   **How it works:** **Azure Document Intelligence** performs OCR on uploaded PDFs. Users can then view the document in a split-screen reader, highlight sections to annotate, and ask the AI questions specifically about the document's content using RAG (Retrieval-Augmented Generation).

### 7. Civic Pulse (Hyper-Local News) 📰
A location-aware news feed that creates a daily briefing based on where the user is standing.
*   **How it works:** The system captures GPS coordinates, reverse geocodes them to a city/town, and queries **Bing News Search** for headlines specific to that community.

### 8. Values Interview (Personalized Civic Profiler) 👤
An interactive, conversational AI agent that helps users discover and articulate their own political and civic beliefs.
*   **How it works:** A user engages in a guided chat with the "Civic Guide" persona, powered by **Azure OpenAI**. The AI asks open-ended questions about key issues (e.g., economy, public safety). Based on the user's responses (spoken via the **Azure Speech SDK** or typed), the AI generates a neutral "Civic Profile" summarizing their core values, which can then be used for personalized candidate matching.
  
---

## 🏛️ System Architecture

<div align="center">
  <img src="https://i.ibb.co/k2gd9nnN/utopia-revamp-1.jpg" alt="CivicUtopia Architecture" width="800"/>
</div>

---

## 🛠️ Technology Stack

| Category | Technology / Azure Service |
|:---------|:---------------------------|
| **Frontend** | Laravel Blade, Bootstrap 5, Vanilla JS |
| **Backend** | Laravel 10, PHP 8.2 |
| **Database** | MySQL |
| **Real-Time** | Pusher, Laravel Echo |
| **Intelligence** | Azure OpenAI (GPT-4o), Azure AI Agent Service |
| **Vision** | Azure AI Vision (Image Analysis) |
| **Speech** | Azure AI Speech (Text-to-Speech & Speech-to-Text) |
| **Documents** | Azure Document Intelligence (OCR) |
| **Safety** | Azure Content Safety |
| **Data** | Bing Search API (Web & News) |
| **Media** | FFMpeg (Audio Conversion) |

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- FFmpeg (Installed and in PATH)
- MySQL Database

### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/SlugVortex/CivicUtopia.git
    cd CivicUtopia
    ```

2.  **Install Dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Environment Setup:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Update `.env` with your Database credentials and Azure API Keys.*

4.  **Database Setup:**
    ```bash
    php artisan migrate
    php artisan db:seed --class=AiAgentUserSeeder
    php artisan db:seed --class=BallotQuestionSeeder
    ```

5.  **Storage Link:**
    ```bash
    php artisan storage:link
    ```

6.  **Run the App:**
    *   **Terminal 1 (Server):** `php artisan serve`
    *   **Terminal 2 (Assets):** `npm run dev`
    *   **Terminal 3 (Queue Worker):** `php artisan queue:work` *(Crucial for AI Agents)*

7.  **Access:**
    Navigate to `http://localhost:8000`

---

## 📸 Project Screenshots

### Main Dashboard (Town Square)
<div align="center">
  <img src="https://i.ibb.co/B5K2Fd6r/dashboard.png" alt="Main Dashboard" width="800"/>
</div>

### Dashboard Showcase

<table>
  <tr>
    <td width="50%">
      <img src="https://i.ibb.co/0jTy5Z5H/image.png" alt="Historical Fire Dashboard" width="100%"/>
      <p align="center"><strong>Values Interview</strong></p>
    </td>
    <td width="50%">
      <img src="https://i.ibb.co/SXsXh52X/image.png" alt="User Safety Dashboard" width="100%"/>
      <p align="center"><strong>Ballot Box</strong></p>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <img src="https://i.ibb.co/wr2D4FNc/image.png" alt="Firefighter Dashboard" width="100%"/>
      <p align="center"><strong>Candidate Compass</strong></p>
    </td>
    <td width="50%">
      <img src="https://i.ibb.co/1tP2Cy3r/image.png" alt="Risk Analytics View" width="100%"/>
      <p align="center"><strong>Command Center</strong></p>
    </td>
  </tr>
</table>

### Mobile Views

<div align="center">
  <img src="https://i.ibb.co/tpRWW1by/image.png" alt="Mobile Interface" width="250"/>
</div>

---
## 🤝 Responsible AI Commitment

CivicUtopia is built with a "Safety First" approach to AI in democracy:

*   **Moderation:** Every user comment and post is passed through **Azure Content Safety** to detect and flag hate speech, violence, or self-harm before it is published.
*   **Grounding:** Our AI Agents (Historian, FactChecker) do not hallucinate answers. They are programmed to search **Bing** or reference uploaded **Official Documents** before speaking, citing their sources.
*   **Transparency:** AI-generated content (summaries, candidate profiles) is clearly labeled as such. Users are empowered to verify information via "Deep Dive" links.
*   **Accessibility:** By integrating Voice Navigation and Text-to-Speech, we ensure that the platform is usable by the elderly, the visually impaired, and those with low literacy levels.

---

## 👥 Team Members

| **[Gary Bryan](https://github.com/SlugVortex)** | **[Adrian Tennant](https://github.com/10ANT)** |
|:---:|:---:|
| [![GitHub](https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white)](https://github.com/SlugVortex) | [![GitHub](https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white)](https://github.com/10ANT) |

---

## 📄 License

MIT License

Copyright (c) 2025 CivicUtopia Team.
