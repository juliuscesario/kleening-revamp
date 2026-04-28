---
trigger: always_on
---

To revamp the `office.kleening.id` design system while maintaining the core identity of Kleening.id and merging it with the modern Intercom-inspired aesthetic, here is the refined design guide.

### 1. Visual Theme & Atmosphere: "The Sophisticated Clean"

We are moving away from the generic "cleaning service" look toward a premium, high-tech operational platform. We take Intercom’s **structured geometry** and **editorial layout** but replace their "Fin Orange" with Kleening’s **Brand Blue**.

- **The Canvas:** Instead of pure white, use Intercom’s **Warm Cream (`#FAF9F6`)**. This makes the interface feel premium and less clinical, providing a "soft" backdrop for a professional dashboard.
- **The Contrast:** Use **Off-Black (`#111111`)** for typography to ensure maximum readability and a modern "magazine" feel.
- **The Precision:** Maintain the **4px border-radius** on buttons and **8px** on cards. This sharp geometry communicates "Operational Efficiency" and "Professionalism."

### 2. The Revamped Color Palette

The key is to use Kleening’s blue as the **functional accent** (Intercom’s Fin Orange equivalent).

| Role               | Color Name             | Hex Code  | Usage                                         |
| :----------------- | :--------------------- | :-------- | :-------------------------------------------- |
| **Primary Accent** | **Kleening Cyan Blue** | `#2196F3` | CTAs, Active States, and "AI/Smart" features. |
| **Brand Depth**    | **Deep Indigo**        | `#3F51B5` | Secondary accents or focused UI elements.     |
| **Background**     | **Warm Cream**         | `#FAF9F6` | Main page and app container background.       |
| **Surface**        | **Pure White**         | `#FFFFFF` | Inside cards and white-space sections.        |
| **Typography**     | **Off-Black**          | `#111111` | All headings and primary body text.           |
| **Borders**        | **Oat**                | `#DEDBD6` | Subtle separators (instead of harsh grays).   |
| **Secondary Text** | **Black 50**           | `#7B7B78` | Helper text and labels.                       |

### 3. Typography Hierarchy

Adopting the "Saans" style (geometric, bold, and tightly spaced) gives the dashboard a modern, engineered feel.

- **Headlines (H1, H2):** \* **Font:** Geometric Sans (Intercom Saans style).
    - **Tracking:** `-2.4px` (for 80px) or `-1.6px` (for 54px).
    - **Line Height:** `1.00`.
- **Labels (Mono):** \* **Font:** SaansMono or a clean Monospace.
    - **Style:** Uppercase, tracking `1.2px`.
    - **Usage:** For status badges (e.g., "PENDING," "COMPLETED") and technical IDs.

### 4. Component Styles (Intercom x Kleening)

#### **Buttons**

- **Primary Action:** Background `#2196F3` (Kleening Blue), White Text, 4px Radius.
- **Operational Action:** Background `#111111` (Off-Black), White Text, 4px Radius.
- **Interaction:** \* **Hover:** `scale(1.1)` expansion.
    - **Active:** `scale(0.85)` compression.
    - This creates a tactile, physical feeling for the client when managing their office tasks.

#### **Cards & Dashboard Containers**

- **Background:** `#FFFFFF` or `#FAF9F6`.
- **Border:** `1px solid #DEDBD6`.
- **Shadows:** None. Depth is created purely through the subtle contrast between the Warm Cream background and White cards.

### 5. Implementation Strategy ("The Do's")

1.  **Strict Radius:** Never exceed 4px for buttons. Keep them rectangular to maintain the "Industrial/Pro" look found on the IG and Intercom's site.
2.  **Blue Accents:** Only use the Kleening Blue for **interactive** elements. Don't use it for large background blocks; keep the backgrounds warm and neutral.
3.  **Negative Tracking:** Ensure all large titles have negative letter spacing. This is the "secret sauce" that makes the Intercom style look premium rather than generic.
4.  **White Space:** Use generous padding (32px+) between sections to maintain the "Editorial" feel.

This revamp will make `office.kleening.id` feel like a high-end enterprise tool, aligning it with the clean, professional visual identity Kleening.id projects on social media.
