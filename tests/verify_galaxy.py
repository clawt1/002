
import asyncio
from playwright.async_api import async_playwright
import os

async def verify_galaxy():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        # Use a path relative to the repo root
        page = await browser.new_page()

        # Load the app
        path = os.path.abspath("index.html")
        await page.goto(f"file://{path}")

        # Clear storage to ensure fresh seed
        await page.evaluate("localStorage.clear()")
        await page.reload()

        # 1. Unlock Admin
        await page.click("button.role:has-text('Administrateur')")
        for digit in "0420":
            await page.click(f"button.pin-btn:text-is('{digit}')")

        await page.wait_for_selector("#workspace:not(.hidden)")
        print("✅ Unlock Admin success")

        # 2. Check VIP Client (Nadia is Gold in seed, ltv=1650)
        # Click search to find Nadia
        await page.click("#searchBtn")
        await page.fill("#omniInput", "Nadia")
        await page.wait_for_selector(".omni-item")
        await page.click(".omni-item:has-text('Nadia')")

        # Verify Gold rank in client card
        await page.wait_for_selector("text=Gold")
        print("✅ Rank Gold detected for Nadia")

        # 3. Setup data for Cross-Sell
        # We need a past sale for Nadia, and a sale for another client with same product + something else
        await page.evaluate("""
            db.sales.push({
                clientId: 'c2', // Nadia
                items: [{ productId: 'p1', variantId: 'v1', qty: 1, price: 10 }],
                total: 10, ts: Date.now() - 100000, tva: { 20: 1.67 }
            });
            db.sales.push({
                clientId: 'c1', // Martin
                items: [
                    { productId: 'p1', variantId: 'v1', qty: 1, price: 10 },
                    { productId: 'p5', variantId: null, qty: 1, price: 35 }
                ],
                total: 45, ts: Date.now() - 50000, tva: { 20: 1.67, 5.5: 1.83 }
            });
            save();
        """)
        await page.reload()

        # 4. Test Sale with Rank Discount
        await page.click("button[data-screen='sell']")
        # Select Nadia
        await page.fill("#sellClientSearch", "Nadia")
        await page.click("button.choice:has-text('Nadia')")

        # Select Amnesia
        await page.click("button.choice:has-text('Amnesia')")
        # Select 10g (80€)
        await page.click("button.cat-btn:has-text('10g')")
        await page.click("#next") # Add to basket

        # Step 3: Recap. Nadia is Gold (10% discount). 80€ -> 72€
        await page.wait_for_selector("text=Remise Rang Gold")
        await page.wait_for_selector("text=72.00€")
        print("✅ Rank Discount (10%) applied correctly")

        # 4. Check Cross-Sell
        # In step 1 (product selection), check for cross-sell suggestions
        # Let's go back to step 1
        await page.click("#prev")
        await page.click("#prev")
        await page.wait_for_selector("text=Suggestion Cross-Sell")
        print("✅ Cross-Sell engine rendering")

        # 5. Complete Sale & Check Fiscal Z
        await page.click("#next") # Back to step 2
        await page.click("#next") # Back to step 3
        await page.click("#next") # Valider
        await page.wait_for_selector("#receipt")
        await page.click("text=Terminer")

        # Go to Admin -> Rapport Z
        await page.click("button[data-screen='admin']")
        await page.click("#zReport")
        await page.wait_for_selector("text=RAPPORT Z FISCAL")
        await page.wait_for_selector("text=VENTILATION TVA")
        print("✅ Fiscal Z Report validated")

        # Final Screenshot
        await page.screenshot(path="galaxy_verification.png", full_page=True)
        await browser.close()

if __name__ == "__main__":
    asyncio.run(verify_galaxy())
