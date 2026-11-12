"""Failure containment for the publisher; no WordPress or network access."""
import importlib.util
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch

spec = importlib.util.spec_from_file_location('sync', Path(__file__).resolve().parents[2] / 'deployment/catalog-quality/catalog-sync.py')
sync = importlib.util.module_from_spec(spec)
spec.loader.exec_module(sync)


class PublishingBoundary(unittest.TestCase):
    def test_failed_health_prevents_any_provider_or_publishing_call(self):
        with patch.object(sync, 'healthy', side_effect=sync.SyncFailure('site_health_failed')), patch.object(sync, 'operator') as operator:
            with self.assertRaisesRegex(sync.SyncFailure, 'site_health_failed'):
                sync.synchronize(Path('/synthetic'), Path('/synthetic'))
            operator.assert_not_called()

    def test_changed_source_set_prevents_publication(self):
        with patch.object(sync, 'healthy'), patch.object(sync, 'operator', return_value={'sources': [{'campaign_id': 8089}]}) as operator:
            with self.assertRaisesRegex(sync.SyncFailure, 'inventory_source_mismatch'):
                sync.synchronize(Path('/synthetic'), Path('/synthetic'))
            self.assertEqual(operator.call_count, 1)

    def test_account_creation_anomaly_stops_before_next_source(self):
        values = [{'sources': [{'campaign_id': source} for source in sync.SOURCES]}, {'published': 1, 'users_created': 1}]
        with patch.object(sync, 'healthy'), patch.object(sync, 'operator', side_effect=values) as operator:
            with self.assertRaisesRegex(sync.SyncFailure, 'publication_result_invalid'):
                sync.synchronize(Path('/synthetic'), Path('/synthetic'))
            self.assertEqual(operator.call_count, 2)

    def test_reconciles_only_after_both_bounded_sources_and_retains_gaps(self):
        calls = []
        def operator(release, run, name, args, **kwargs):
            calls.append((name, args))
            if name == 'catalog-inventory.php':
                return {'sources': [{'campaign_id': source} for source in sync.SOURCES]}
            if name == 'catalog-backfill.php':
                self.assertEqual(args[3:], ['20', 'adopt-managed-drafts'])
                return {'published': 0, 'users_created': 0}
            return {'snapshot': {'checked_at': '2026-11-12 14:00:00', 'sources': [{'unresolved_items': 1, 'draft': 3}]}}
        with patch.object(sync, 'healthy'), patch.object(sync, 'operator', side_effect=operator), patch.object(sync, 'archive_owned_receipts'):
            result = sync.synchronize(Path('/synthetic'), Path('/synthetic'))
        self.assertEqual(len(calls), 4)
        self.assertEqual(calls[-1][1][-1], 'verify-source-credit')
        self.assertEqual(result['sources'][0]['unresolved_items'], 1)

    def test_archive_preserves_exact_bytes_and_only_compresses_own_large_files(self):
        with tempfile.TemporaryDirectory() as folder:
            root = Path(folder)
            content = b'{"private_inventory": "' + b'x' * 10000 + b'"}'
            (root / 'inventory.json').write_bytes(content)
            (root / 'summary.json').write_text('{}')
            sync.archive_owned_receipts(root)
            self.assertEqual(sync.gzip.decompress((root / 'inventory.json.gz').read_bytes()), content)
            self.assertTrue((root / 'summary.json').is_file())
            self.assertFalse((root / 'inventory.json').exists())


if __name__ == '__main__':
    unittest.main()
