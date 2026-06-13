import { test, expect } from '@playwright/test';

const qaLogin = process.env.PW_QA_LOGIN;
const qaPassword = process.env.PW_QA_PASSWORD;
const teamUserId = process.env.PW_TEAM_USER_ID ?? '2';

test.describe('admin qa smoke', () => {
  test.skip(!qaLogin || !qaPassword, 'Set PW_QA_LOGIN and PW_QA_PASSWORD to run admin QA smoke.');

  test('admin key pages render and stay usable', async ({ page }, testInfo) => {
    await page.goto('/login');
    await page.getByLabel(/email or username/i).fill(qaLogin ?? '');
    await page.getByLabel(/^password$/i).fill(qaPassword ?? '');

    await Promise.all([
      page.waitForURL(/\/admin/),
      page.getByRole('button', { name: /login/i }).click(),
    ]);

    await test.step('settings loads', async () => {
      await page.goto('/admin/settings');
      await expect(page.getByRole('heading', { name: /settings/i })).toBeVisible();
      await page.screenshot({
        path: testInfo.outputPath('admin-settings.png'),
        fullPage: true,
      });
    });

    await test.step('invoices index loads', async () => {
      await page.goto('/admin/invoices');
      await expect(page.getByRole('table', { name: /invoices list/i })).toBeVisible();
      await expect(page.locator('body')).toContainText('Create invoice');
      await page.screenshot({
        path: testInfo.outputPath('admin-invoices.png'),
        fullPage: true,
      });
    });

    await test.step('menu admin loads', async () => {
      await page.goto('/admin/menu');
      await expect(page.locator('body')).toContainText('Save Menu');
      await page.screenshot({
        path: testInfo.outputPath('admin-menu.png'),
        fullPage: true,
      });
    });

    await test.step('team incidents tab loads', async () => {
      await page.goto(`/team/${teamUserId}?tab=incidents`);
      await expect(page.locator('body')).toContainText('Incidents');
      await page.screenshot({
        path: testInfo.outputPath('admin-team-incidents.png'),
        fullPage: true,
      });
    });

    await test.step('calendar loads and events surface is visible', async () => {
      await page.goto('/admin/calendar');
      await expect(page.locator('body')).toContainText('Reservation schedule overview');
      await expect(page.locator('.cal-wrap')).toBeVisible();
      await page.screenshot({
        path: testInfo.outputPath('admin-calendar.png'),
        fullPage: true,
      });
    });
  });
});
